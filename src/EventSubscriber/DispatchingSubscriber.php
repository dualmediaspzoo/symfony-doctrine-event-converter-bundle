<?php

namespace DualMedia\DoctrineEventConverterBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\PersistentCollection;
use DualMedia\DoctrineEventConverterBundle\Event\AbstractEntityEvent;
use DualMedia\DoctrineEventConverterBundle\Interfaces\EntityInterface;
use DualMedia\DoctrineEventConverterBundle\Model\Event;
use DualMedia\DoctrineEventConverterBundle\Model\SubEvent;
use DualMedia\DoctrineEventConverterBundle\Service\DelayableEventDispatcher;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class DispatchingSubscriber implements EventSubscriber
{
    /**
     * List of events to be dispatched after entity changes.
     *
     * @var non-empty-array<string, array<class-string<EntityInterface>, list<Event>>>
     */
    private array $mainEventList = [
        Events::postPersist => [], Events::postUpdate => [], Events::postRemove => [],
        Events::prePersist => [], Events::preUpdate => [], Events::preRemove => [],
    ];

    /**
     * @var array<class-string<EntityInterface>, array<int, array<class-string<AbstractEntityEvent>, non-empty-list<SubEvent>>>>
     */
    private array $subEventList = [];

    private bool $subEventsOptimized = false;
    private bool $preFlush = false;


    /**
     * ID cache for removed entities so their ids can be temporarily remembered.
     *
     * @var array<string, string|int>
     */
    private array $removeIdCache = [];

    /**
     * Entity change sets.
     *
     * @var array<string, array<string, array<int, mixed>|PersistentCollection>>
     */
    private array $updateObjectCache = [];

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::postPersist,
            Events::preUpdate,
            Events::postUpdate,
            Events::preRemove,
            Events::postRemove,
            Events::preFlush,
            Events::postFlush,
        ];
    }

    public function __construct(
        //        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly DelayableEventDispatcher $eventDispatcher,
        private readonly PropertyAccessor $propertyAccess = new PropertyAccessor()
    ) {
    }

    /**
     * Registers an event for use later by the dispatcher
     *
     * @param class-string<AbstractEntityEvent> $eventClass
     * @param non-empty-list<class-string<EntityInterface>> $entities
     * @param string $event
     *
     * @return void
     *
     * @internal
     */
    public function registerEvent(
        string $eventClass,
        array $entities,
        string $event,
        bool $afterFlush = false,
    ): void {
        if (!array_key_exists($event, $this->mainEventList)) {
            return;
        }

        foreach ($entities as $class) {
            if (!isset($this->mainEventList[$event][$class])) {
                $this->mainEventList[$event][$class] = []; // @phpstan-ignore-line
            }

            $this->mainEventList[$event][$class][] = new Event($eventClass, $afterFlush);
        }
    }

    /**
     * Gets the list of for an entity and specified Doctrine {@see Events}
     *
     * @param string $type
     * @param class-string<EntityInterface> $entity
     *
     * @return list<Event>
     */
    public function getEvents(
        string $type,
        string $entity
    ): array {
        return $this->mainEventList[$type][$entity] ?? [];
    }

    /**
     * Registers a sub event for use later by the dispatcher
     *
     * @param class-string<AbstractEntityEvent> $eventClass
     * @param non-empty-list<class-string<EntityInterface>> $entities
     * @param bool $allMode
     * @param array<string, null|array{0?: mixed, 1: mixed}> $fieldList
     * @param array<string, mixed> $requirements
     * @param list<string> $types
     * @param int $priority higher means the event will be checked/fired faster
     *
     * @return void
     *
     * @internal
     */
    public function registerSubEvent(
        string $eventClass,
        array $entities,
        bool $allMode,
        array $fieldList,
        array $requirements,
        array $types,
        int $priority = 0,
        bool $afterFlush = false,
    ): void {
        foreach ($entities as $entity) {
            if (!isset($this->subEventList[$entity])) {
                $this->subEventList[$entity] = [];
            }

            if (!isset($this->subEventList[$entity][$priority])) {
                $this->subEventList[$entity][$priority] = [];
            }

            if (!isset($this->subEventList[$entity][$priority][$eventClass])) {
                $this->subEventList[$entity][$priority][$eventClass] = []; // @phpstan-ignore-line
            }
            $this->subEventList[$entity][$priority][$eventClass][] = new SubEvent($allMode, $fieldList, $requirements, $types, $afterFlush); // @phpstan-ignore-line
        }
    }

    /**
     * Gets the list of SubEvents for an entity
     *
     * @param class-string<EntityInterface> $entity
     *
     * @return array<int, array<class-string<AbstractEntityEvent>, list<SubEvent>>>
     */
    public function getSubEvents(
        string $entity
    ): array {
        return $this->subEventList[$entity] ?? [];
    }

    /**
     * @param LifecycleEventArgs $args
     *
     * @internal
     */
    public function prePersist(
        LifecycleEventArgs $args
    ): void {
        if ($args->getObject() instanceof EntityInterface) {
            $this->preRunEvents(Events::prePersist, $args->getObject());
        }
    }

    /**
     * @internal
     */
    public function preFlush(
        PostFlushEventArgs $args
    ): void {
        $this->preFlush = true;
    }

    /**
     * @internal
     */
    public function postFlush(
        PostFlushEventArgs $args
    ): void {
        $this->eventDispatcher->submitDelayed();
        $this->preFlush = false;
    }

    /**
     * @internal
     */
    public function postPersist(
        LifecycleEventArgs $args
    ): void {
        $this->preRunEvents(Events::postPersist, $args->getObject());
    }

    /**
     * @param PreUpdateEventArgs $args
     *
     * @internal
     */
    public function preUpdate(
        PreUpdateEventArgs $args
    ): void {
        $changes = [];
        if ($args->getObject() instanceof EntityInterface) {
            $changes = $this->updateObjectCache[spl_object_hash($args->getObject())] = $args->getEntityChangeSet();
        }
        $this->preRunEvents(Events::preUpdate, $args->getObject(), null, $changes);
    }

    /**
     * @param LifecycleEventArgs $args
     *
     * @internal
     */
    public function postUpdate(
        LifecycleEventArgs $args
    ): void {
        $hash = spl_object_hash($args->getObject());
        $changes = $this->updateObjectCache[$hash] ?? [];
        unset($this->updateObjectCache[$hash]);
        $this->preRunEvents(Events::postUpdate, $args->getObject(), null, $changes);
    }

    /**
     * @param LifecycleEventArgs $args
     *
     * @internal
     */
    public function preRemove(
        LifecycleEventArgs $args
    ): void {
        if ($args->getObject() instanceof EntityInterface) {
            $this->removeIdCache[spl_object_hash($args->getObject())] = $args->getObject()->getId(); // @phpstan-ignore-line
        }
        $this->preRunEvents(Events::preRemove, $args->getObject());
    }

    /**
     * @param LifecycleEventArgs $args
     *
     * @internal
     */
    public function postRemove(
        LifecycleEventArgs $args
    ): void {
        $hash = spl_object_hash($args->getObject());
        if (isset($this->removeIdCache[$hash])) {
            $id = $this->removeIdCache[$hash];
            unset($this->removeIdCache[$hash]);
            $this->preRunEvents(Events::postRemove, $args->getObject(), $id);
        }
    }

    /**
     * @param string $event
     * @param object $obj
     * @param int|string|null $id
     * @param array<string, array<int, mixed>|PersistentCollection> $changes
     */
    private function preRunEvents(
        string $event,
        object $obj,
        int|string|null $id = null,
        array $changes = []
    ): void {
        $events = $this->mainEventList[$event];
        $class = ClassUtils::getClass($obj);
        if (!array_key_exists($class, $events)) {
            return;
        }

        /**
         * As no non-EntityInterface object can exist in the mainEventList, we don't need to validate type in theory
         *
         * @noinspection PhpParamsInspection
         * @phpstan-ignore-next-line
         */
        $this->runEvents($event, $events[$class], $obj, $id, $changes);
    }

    /**
     * @param string $type
     * @param list<Event> $events
     * @param EntityInterface $obj
     * @param int|string|null $id
     * @param array<string, array<int, mixed>|PersistentCollection> $changes
     *
     * @return void
     */
    private function runEvents(
        string $type,
        array $events,
        EntityInterface $obj,
        int|string|null $id = null,
        array $changes = []
    ): void {
        foreach ($events as $model) {
            /**
             * @var AbstractEntityEvent $event
             * @var Event $model
             */
            $event = (new $model->eventClass());

            $event->setEntity($obj)
                ->setEventType($type)
                ->setChanges($changes)
                ->setDeletedId($id);

            if ($this->preFlush) {
                $this->eventDispatcher->clearEvents();
                $this->preFlush = false;
            }

            $this->eventDispatcher->dispatch($event, $model->afterFlush);

            $this->runSubEvents($event);
        }
    }

    private function runSubEvents(
        AbstractEntityEvent $event
    ): void {
        $entity = $event->getEntity();
        $class = ClassUtils::getClass($entity);

        if (!isset($this->subEventList[$class])) { // No events found, simply exit
            return;
        }
        $this->optimizeSubEvents();

        foreach ($this->subEventList[$class] as $list) {
            foreach ($list as $eventClass => $models) {
                foreach ($models as $model) {
                    if (!in_array($event->getEventType(), $model->types, true)) {
                        continue; // Create event only for selected event types e.g. added, removed
                    }

                    if (!$this->validateSubEvent($event->getChanges(), $model, $entity, $event->getEventType())) { // @phpstan-ignore-line
                        continue;
                    }

                    /** @var AbstractEntityEvent $subEvent */
                    $subEvent = (new $eventClass());

                    $subEvent->setEntity($entity)
                        ->setChanges(array_intersect_key(
                            $event->getChanges(),
                            $model->fieldList
                        )) // save only fields that the event requested, ignore rest
                        ->setEventType($event->getEventType());

                    $this->eventDispatcher->dispatch($subEvent, $model->afterFlush);

                    break;
                }
            }
        }
    }

    /**
     * @param array<string, array<int, mixed>> $eventChanges
     * @param SubEvent $model
     * @param EntityInterface $entity
     * @param string $event
     * @return bool
     */
    private function validateSubEvent(
        array $eventChanges,
        SubEvent $model,
        EntityInterface $entity,
        string $event
    ): bool {
        if (in_array($event, [Events::postUpdate, Events::preUpdate], true)) {
            if ($model->allMode && count(array_diff_key($model->fieldList, $eventChanges))) { // Event contains keys that haven't changed
                return false;
            } elseif (!$model->allMode && !count(array_intersect_key($eventChanges, $model->fieldList))) { // Event doesn't contain any of the required keys
                return false;
            }

            $validFields = [];

            foreach ($eventChanges as $field => $fields) {
                if (!array_key_exists($field, $model->fieldList)) {
                    continue;
                } elseif (null === ($modelWantedState = $model->fieldList[$field])) {
                    // if you set null instead of setting null for key 0 you're dumb and #wontfix
                    $validFields[$field] = true;
                    continue;
                }

                $count = count($modelWantedState);

                if (1 === $count) {
                    $existingCounter = isset($modelWantedState[0]) ? 0 : 1;
                    $validFields[$field] = $this->stateEquals($fields[$existingCounter], $modelWantedState[$existingCounter]);
                } elseif (2 === $count) {
                    /** @var array{0: mixed, 1: mixed} $modelWantedState */
                    $validFields[$field] = $this->stateEquals($fields[0], $modelWantedState[0]) && $this->stateEquals($fields[1], $modelWantedState[1]);
                }
            }

            $reduced = array_reduce($validFields, fn ($carry, $data) => $carry + ((int) $data));

            if (!(!$model->allMode ? $reduced > 0 : $reduced === count($model->fieldList))) {
                return false;
            }
        }

        foreach ($model->requirements as $fieldName => $value) {
            try {
                if ($this->propertyAccess->getValue($entity, $fieldName) !== $value) {
                    return false;
                }
            } catch (\Throwable) {
                return false;
            }
        }

        return true;
    }

    private function stateEquals(
        mixed $known,
        mixed $expected
    ): bool {
        if ($known === $expected) {
            return true;
        }

        if (!($known instanceof \BackedEnum) && ($expected instanceof \BackedEnum)) {
            return $known === $expected->value;
        }

        return false;
    }

    private function optimizeSubEvents(): void
    {
        if ($this->subEventsOptimized) {
            return;
        }

        foreach ($this->subEventList as $entity => $priorities) {
            ksort($this->subEventList[$entity]);
            $this->subEventList[$entity] = array_reverse($this->subEventList[$entity], true);
        }

        $this->subEventsOptimized = true;
    }
}
