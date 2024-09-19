<?php

namespace DualMedia\DoctrineEventConverterBundle\EventSubscriber;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\PersistentCollection;
use DualMedia\DoctrineEventConverterBundle\Event\AbstractEntityEvent;
use DualMedia\DoctrineEventConverterBundle\Interfaces\EntityInterface;
use DualMedia\DoctrineEventConverterBundle\Model\Event;
use DualMedia\DoctrineEventConverterBundle\Service\DelayableEventDispatcher;
use DualMedia\DoctrineEventConverterBundle\Service\EventService;
use DualMedia\DoctrineEventConverterBundle\Service\SubEventService;
use DualMedia\DoctrineEventConverterBundle\Service\VerifierService;

class DispatchingSubscriber
{
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

    public function __construct(
        private readonly EventService $eventService,
        private readonly SubEventService $subEventService,
        private readonly VerifierService $verifierService,
        private readonly DelayableEventDispatcher $dispatcher,
    ) {
    }

    public function prePersist(
        PrePersistEventArgs $args,
    ): void {
        if ($args->getObject() instanceof EntityInterface) {
            $this->process(Events::prePersist, $args->getObject());
        }
    }

    public function preFlush(
        PreFlushEventArgs $args,
    ): void {
        $this->preFlush = true;
    }

    public function postFlush(
        PostFlushEventArgs $args,
    ): void {
        $this->dispatcher->submitDelayed();
        $this->preFlush = false;
    }

    public function postPersist(
        PostPersistEventArgs $args,
    ): void {
        $this->process(Events::postPersist, $args->getObject());
    }

    public function preUpdate(
        PreUpdateEventArgs $args,
    ): void {
        $changes = [];
        $object = $args->getObject();

        if ($object instanceof EntityInterface) {
            $changes = $this->updateObjectCache[spl_object_hash($object)] = $args->getEntityChangeSet();
        }
        $this->process(Events::preUpdate, $object, null, $changes);
    }

    public function postUpdate(
        PostUpdateEventArgs $args,
    ): void {
        $object = $args->getObject();
        $hash = spl_object_hash($object);
        $changes = $this->updateObjectCache[$hash] ?? [];
        unset($this->updateObjectCache[$hash]);

        $this->process(Events::postUpdate, $object, null, $changes);
    }

    public function preRemove(
        PreRemoveEventArgs $args,
    ): void {
        $object = $args->getObject();

        if ($args->getObject() instanceof EntityInterface) {
            $this->removeIdCache[spl_object_hash($object)] = $object->getId(); // @phpstan-ignore-line
        }
        $this->process(Events::preRemove, $object);
    }

    public function postRemove(
        PostRemoveEventArgs $args,
    ): void {
        $object = $args->getObject();
        $hash = spl_object_hash($object);

        if (isset($this->removeIdCache[$hash])) {
            $id = $this->removeIdCache[$hash];
            unset($this->removeIdCache[$hash]);
            $this->process(Events::postRemove, $object, $id);
        }
    }

    /**
     * @param array<string, array<int, mixed>|PersistentCollection> $changes
     */
    private function process(
        string $type,
        object $obj,
        int|string|null $id = null,
        array $changes = [],
    ): void {
        $class = ClassUtils::getClass($obj);

        foreach ($this->eventService->get($type, $class) as $model) {
            /**
             * As EntityInterface is validated during cache generation there is no point in checking it here again.
             *
             * @var EntityInterface $obj
             */

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
                $this->dispatcher->clearEvents();
                $this->preFlush = false;
            }

            $this->dispatcher->dispatch($event, $model->afterFlush);

            $this->subEvents($event);
        }
    }

    private function subEvents(
        AbstractEntityEvent $event,
    ): void {
        $entity = $event->getEntity();
        $class = ClassUtils::getClass($entity);

        foreach ($this->subEventService->get($class) as $eventClass => $models) {
            foreach ($models as $model) {
                if (!$this->verifierService->validate($event->getChanges(), $model, $entity, $event->getEventType())) { // @phpstan-ignore-line
                    continue;
                }

                /** @var AbstractEntityEvent $subEvent */
                $subEvent = (new $eventClass());

                $subEvent->setEntity($entity)
                    ->setChanges(array_intersect_key(
                        $event->getChanges(),
                        $model->fields
                    )) // save only fields that the event requested, ignore rest
                    ->setEventType($event->getEventType());

                $this->dispatcher->dispatch($subEvent, $model->afterFlush);

                break;
            }
        }
    }
}
