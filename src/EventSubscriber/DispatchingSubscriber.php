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
use DualMedia\DoctrineEventConverterBundle\Storage\EventService;
use DualMedia\DoctrineEventConverterBundle\Storage\SubEventService;
use DualMedia\DoctrineEventConverterBundle\Verifier\SubEventVerifier;

/**
 * @phpstan-import-type DoctrineChangeArray from DoctrineEventConverterBundle
 */
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
     * @var array<string, DoctrineChangeArray>
     */
    private array $updateObjectCache = [];

    public function __construct(
        private readonly EventService $eventService,
        private readonly SubEventService $subEventService,
        private readonly DelayableEventDispatcher $dispatcher,
        private readonly SubEventVerifier $subEventVerifier
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

        /**
         * As EntityInterface is validated during cache generation there is no point in checking it here again.
         *
         * @var EntityInterface $obj
         */

        /**
         * @var AbstractEntityEvent<EntityInterface> $event
         */
        $event = (new $model->eventClass());

        $event->setEntity($obj)
            ->setEventType($type)
            ->setChanges($changes)
            ->setDeletedId($id);

        if ($this->preFlush) {
            $this->dispatcher->clear();
            $this->preFlush = false;
        }

        $this->dispatcher->dispatch($event, $model->afterFlush);

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

            $subEvent = (new $model->eventClass());

            $subEvent->setEntity($entity)
                ->setChanges(array_intersect_key(
                    $changes,
                    $model->fields
                )) // save only fields that the event requested, ignore rest
                ->setEventType($type);

            $this->dispatcher->dispatch($subEvent, $model->afterFlush);
        }
    }
}
