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
use DualMedia\DoctrineEventConverterBundle\DelayableEventDispatcher;
use DualMedia\DoctrineEventConverterBundle\DoctrineEventConverterBundle;
use DualMedia\DoctrineEventConverterBundle\Event\AbstractEntityEvent;
use DualMedia\DoctrineEventConverterBundle\Interface\EntityInterface;
use DualMedia\DoctrineEventConverterBundle\Model\Delayed;
use DualMedia\DoctrineEventConverterBundle\ObjectIdCache;
use DualMedia\DoctrineEventConverterBundle\Storage\EventService;
use DualMedia\DoctrineEventConverterBundle\Storage\SubEventService;
use DualMedia\DoctrineEventConverterBundle\Verifier\SubEventVerifier;

/**
 * @phpstan-import-type DoctrineChangeArray from DoctrineEventConverterBundle
 */
class DispatchingSubscriber
{
    /**
     * Depth marker for delayed events.
     */
    private int $depth = 0;

    /**
     * Entity change sets.
     *
     * @var array<string, DoctrineChangeArray>
     */
    private array $updateObjectCache = [];

    public function __construct(
        private readonly EventService $eventService,
        private readonly SubEventService $subEventService,
        private readonly DelayableEventDispatcher $dispatcher,
        private readonly SubEventVerifier $subEventVerifier,
        private readonly ObjectIdCache $objectIdCache
    ) {
    }

    public function preFlush(
        PreFlushEventArgs $args,
    ): void {
        $this->dispatcher->clear(++$this->depth);
    }

    public function postFlush(
        PostFlushEventArgs $args,
    ): void {
        $this->dispatcher->submitDelayed($this->depth--);
    }

    public function prePersist(
        PrePersistEventArgs $args,
    ): void {
        $object = $args->getObject();

        if (!($object instanceof EntityInterface)) {
            return;
        }

        $this->process(Events::prePersist, $object);
    }

    public function postPersist(
        PostPersistEventArgs $args,
    ): void {
        $object = $args->getObject();
        $this->objectIdCache->set($object);

        $this->process(Events::postPersist, $object);
    }

    public function preUpdate(
        PreUpdateEventArgs $args,
    ): void {
        $changes = [];
        $object = $args->getObject();

        if ($object instanceof EntityInterface) {
            $changes = $this->updateObjectCache[spl_object_hash($object)] = $args->getEntityChangeSet(); // @phpstan-ignore-line
            /** @var DoctrineChangeArray $changes */
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

        if ($object instanceof EntityInterface) {
            $this->objectIdCache->set($object);
        }

        $this->process(Events::preRemove, $object);
    }

    public function postRemove(
        PostRemoveEventArgs $args,
    ): void {
        $object = $args->getObject();

        if (null === ($id = $this->objectIdCache->get($object))) {
            return;
        }

        $this->process(Events::postRemove, $object, $id);
    }

    /**
     * @param string $type one of {@link Events}
     * @param DoctrineChangeArray $changes
     */
    private function process(
        string $type,
        object $obj,
        int|string|null $id = null,
        array $changes = [],
    ): void {
        $class = ClassUtils::getClass($obj);

        if (null === ($model = $this->eventService->get($type, $class))) {
            return;
        }

        /** @var class-string<EntityInterface> $class */
        /**
         * As EntityInterface is validated during cache generation there is no point in checking it here again.
         *
         * @var EntityInterface $obj
         */

        /**
         * @var AbstractEntityEvent<EntityInterface> $event
         */
        $event = new $model->eventClass();

        $event->setEntity($obj)
            ->setEventType($type)
            ->setChanges($changes)
            ->setDeletedId($id);

        if (!$model->afterFlush) {
            $this->dispatcher->dispatch($event);
        } else {
            $this->dispatcher->delay(
                new Delayed(
                    $event,
                    $class,
                    spl_object_hash($obj),
                    $obj->getId()
                ),
                $this->depth
            );
        }

        $this->subEvents($event);
    }

    /**
     * @param AbstractEntityEvent<EntityInterface> $event
     */
    private function subEvents(
        AbstractEntityEvent $event,
    ): void {
        $entity = $event->getEntity();
        $class = ClassUtils::getClass($entity);
        $changes = $event->getChanges();
        $type = $event->getEventType();

        foreach ($this->subEventService->get($class) as $model) {
            if (!$this->subEventVerifier->verify($entity, $model, $changes, $type)) {
                continue;
            }

            /** @var AbstractEntityEvent<EntityInterface> $subEvent */
            $subEvent = new $model->eventClass();

            $subEvent->setChanges(array_intersect_key(
                $changes,
                $model->fields
            )) // save only fields that the event requested, ignore rest
                ->setEventType($type);

            if (!$model->afterFlush) {
                $this->dispatcher->dispatch(
                    $subEvent->setEntity($entity)
                );
            } else {
                $this->dispatcher->delay(
                    new Delayed(
                        $subEvent,
                        $class,
                        spl_object_hash($entity),
                        $entity->getId()
                    ),
                    $this->depth
                );
            }
        }
    }
}
