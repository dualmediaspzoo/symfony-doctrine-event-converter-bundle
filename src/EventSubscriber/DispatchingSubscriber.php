<?php

namespace DM\DoctrineEventDistributorBundle\EventSubscriber;

use DM\DoctrineEventDistributorBundle\Event\AbstractEntityEvent;
use DM\DoctrineEventDistributorBundle\Event\DispatchEvent;
use DM\DoctrineEventDistributorBundle\Interfaces\EntityInterface;
use DM\DoctrineEventDistributorBundle\Model\SubEvent;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DispatchingSubscriber implements EventSubscriber
{
    /**
     * List of events to be dispatched after entity changes.
     *
     * @var array[]
     *
     * @psalm-var array<string, array<class-string<EntityInterface>, non-empty-list<class-string<AbstractEntityEvent>>>>
     */
    private array $mainEventList = [
        Events::postPersist => [], Events::postUpdate => [], Events::postRemove => [],
        Events::prePersist => [], Events::preUpdate => [], Events::preRemove => [],
    ];

    /**
     * @var array[][][]
     *
     * @psalm-var array<class-string<EntityInterface>, array<int, array<class-string<AbstractEntityEvent>, non-empty-list<SubEvent>>>>
     */
    private array $subEventList = [];

    private bool $subEventsOptimized = false;

    /**
     * ID cache for removed entities so their ids can be temporarily remembered.
     *
     * @var array
     * @psalm-var array<string, string|int>
     */
    private array $removeIdCache = [];

    /**
     * Entity change sets.
     *
     * @var array<string, array<string,array<int,mixed>>>
     */
    private array $updateObjectCache = [];

    private EventDispatcherInterface $eventDispatcher;
    private PropertyAccessor $propertyAccess;

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::postPersist,
            Events::preUpdate,
            Events::postUpdate,
            Events::preRemove,
            Events::postRemove,
        ];
    }

    public function __construct(
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->propertyAccess = new PropertyAccessor();
    }

    /**
     * Registers an event for use later by the dispatcher
     *
     * @param string $eventClass
     * @psalm-param class-string<AbstractEntityEvent> $eventClass
     * @param string[] $entities
     * @psalm-param non-empty-list<class-string<EntityInterface>> $entities
     * @param string $event
     *
     * @return void
     *
     * @internal
     */
    public function registerEvent(
        string $eventClass,
        array $entities,
        string $event
    ): void {
        if (!array_key_exists($event, $this->mainEventList)) {
            return;
        }

        foreach ($entities as $class) {
            if (!isset($this->mainEventList[$event][$class])) {
                $this->mainEventList[$event][$class] = [];
            }

            $this->mainEventList[$event][$class][] = $eventClass;
        }
    }

    /**
     * Gets the list of for an entity and specified Doctrine {@see Events}
     *
     * @param string $type
     * @psalm-param Events::postPersist|Events::postUpdate|Events::postRemove|Events::prePersist|Events::preUpdate|Events::preRemove $type
     * @param string $entity
     * @psalm-param class-string<EntityInterface> $entity
     *
     * @return string[]
     * @psalm-return list<class-string<AbstractEntityEvent>>
     *
     * @psalm-immutable
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
     * @param string $eventClass
     * @psalm-param class-string<AbstractEntityEvent> $eventClass
     * @param string[] $entities
     * @psalm-param non-empty-list<class-string<EntityInterface>> $entities
     * @param bool $allMode
     * @param array $fieldList
     * @param array $requirements
     * @param array $types
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
        int $priority = 0
    ): void {
        foreach ($entities as $entity) {
            if (!isset($this->subEventList[$entity])) {
                $this->subEventList[$entity] = [];
            }

            if (!isset($this->subEventList[$entity][$priority])) {
                $this->subEventList[$entity][$priority] = [];
            }

            if (!isset($this->subEventList[$entity][$priority][$eventClass])) {
                $this->subEventList[$entity][$priority][$eventClass] = [];
            }

            $this->subEventList[$entity][$priority][$eventClass][] = new SubEvent($allMode, $fieldList, $requirements, $types);
        }
    }

    /**
     * Gets the list of SubEvents for an entity
     *
     * @param string $entity
     * @psalm-param class-string<EntityInterface> $entity
     *
     * @return array
     * @psalm-return array<int, array<class-string<AbstractEntityEvent>, list<SubEvent>>>
     *
     * @psalm-immutable
     */
    public function getSubEvents(
        string $entity
    ): array {
        return $this->subEventList[$entity] ?? [];
    }

    /**
     * @internal
     * @param LifecycleEventArgs $args
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
     * @param LifecycleEventArgs $args
     */
    public function postPersist(
        LifecycleEventArgs $args
    ): void {
        $this->preRunEvents(Events::postPersist, $args->getObject());
    }

    /**
     * @internal
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(
        PreUpdateEventArgs $args
    ): void {
        $changes = [];
        if ($args->getObject() instanceof EntityInterface) {
            /** This errors on some doctrine combinations in CI */
            /** @psalm-suppress InvalidPropertyAssignmentValue */
            $changes = $this->updateObjectCache[spl_object_hash($args->getObject())] = $args->getEntityChangeSet();
        }
        $this->preRunEvents(Events::preUpdate, $args->getObject(), null, $changes);
    }

    /**
     * @internal
     * @param LifecycleEventArgs $args
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
     * @internal
     * @param LifecycleEventArgs $args
     */
    public function preRemove(
        LifecycleEventArgs $args
    ): void {
        if ($args->getObject() instanceof EntityInterface) {
            $this->removeIdCache[spl_object_hash($args->getObject())] = $args->getObject()->getId();
        }
        $this->preRunEvents(Events::preRemove, $args->getObject());
    }

    /**
     * @internal
     * @param LifecycleEventArgs $args
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
     * @param object|EntityInterface $obj
     * @param int|string|null $id
     * @param array $changes
     *
     * @return void
     */
    private function preRunEvents(
        string $event,
        $obj,
        $id = null,
        array $changes = []
    ): void {
        $events = $this->mainEventList[$event];
        $class = ClassUtils::getClass($obj);
        if (!array_key_exists($class, $events)) {
            return;
        }

        $this->runEvents($event, $events[$class], $obj, $id, $changes);
    }

    /**
     * @param string $type
     * @param array $events
     * @psalm-param class-string[] $events
     * @param EntityInterface $obj
     * @param int|string|null $id
     * @param array $changes
     *
     * @return void
     */
    private function runEvents(
        string $type,
        array $events,
        EntityInterface $obj,
        $id = null,
        array $changes = []
    ): void {
        foreach ($events as $eventClass) {
            /**
             * @var AbstractEntityEvent $event
             */
            $event = (new $eventClass());

            $event->setEntity($obj)
                ->setEventType($type)
                ->setChanges($changes)
                ->setDeletedId($id);

            $this->eventDispatcher->dispatch($event);
            $this->eventDispatcher->dispatch(new DispatchEvent($event));
            $this->runSubEvents($event);
        }
    }

    private function runSubEvents(
        AbstractEntityEvent $event
    ) {
        $entity = $event->getEntity();
        $class = ClassUtils::getClass($entity);

        if (!isset($this->subEventList[$class])) { // No events found, simply exit
            return;
        }
        $this->optimizeSubEvents();

        foreach ($this->subEventList[$class] as $list) {
            foreach ($list as $eventClass => $models) {
                foreach ($models as $model) {
                    if (!in_array($event->getEventType(), $model->getTypes(), true)) {
                        continue; // Create event only for selected event types e.g. added, removed
                    }

                    if (!$this->validateSubEvent($event->getChanges(), $model, $entity, $event->getEventType())) {
                        continue;
                    }

                    /** @var AbstractEntityEvent $subEvent */
                    $subEvent = (new $eventClass());

                    $subEvent->setEntity($entity)
                        ->setChanges(array_intersect_key(
                            $event->getChanges(),
                            $model->getFieldList()
                        )) // save only fields that the event requested, ignore rest
                        ->setEventType($event->getEventType());

                    $this->eventDispatcher->dispatch($subEvent);
                    $this->eventDispatcher->dispatch(new DispatchEvent($subEvent));
                    break;
                }
            }
        }
    }

    private function validateSubEvent(
        array $eventChanges,
        SubEvent $model,
        EntityInterface $entity,
        string $event
    ): bool {
        if (in_array($event, [Events::postUpdate, Events::preUpdate], true)) {
            if ($model->isAllMode() && count(array_diff_key($model->getFieldList(), $eventChanges))) { // Event contains keys that haven't changed
                return false;
            } elseif (!$model->isAllMode() && !count(array_intersect_key($eventChanges, $model->getFieldList()))) { // Event doesn't contain any of the required keys
                return false;
            }

            $validFields = [];

            foreach ($eventChanges as $field => $fields) {
                if (!array_key_exists($field, $model->getFieldList())) {
                    continue;
                } elseif (null === ($modelWantedState = $model->getFieldList()[$field])) {
                    // if you set null instead of setting null for key 0 you're dumb and #wontfix
                    $validFields[$field] = true;
                    continue;
                }

                /**
                 * This is required because of a bug in psalm which does not correctly infer the array item count
                 *
                 * @var int $count
                 * @noinspection PhpRedundantVariableDocTypeInspection
                 */
                $count = count($modelWantedState);

                if (1 === $count) {
                    $validFields[$field] = $fields[1] === $modelWantedState[0];
                } elseif (2 === $count) {
                    $validFields[$field] = $fields[0] === $modelWantedState[0] && $fields[1] === $modelWantedState[1];
                }
            }

            $reduced = array_reduce($validFields, fn ($carry, $data) => $carry + ((int) $data));

            if (!(!$model->isAllMode() ? $reduced > 0 : $reduced === count($model->getFieldList()))) {
                return false;
            }
        }

        foreach ($model->getRequirements() as $fieldName => $value) {
            try {
                if ($this->propertyAccess->getValue($entity, $fieldName) !== $value) {
                    return false;
                }
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
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
