<?php

namespace DualMedia\DoctrineEventConverterBundle;

use DualMedia\DoctrineEventConverterBundle\Event\AbstractEntityEvent;
use DualMedia\DoctrineEventConverterBundle\Event\DispatchEvent;
use DualMedia\DoctrineEventConverterBundle\Interface\EntityInterface;
use DualMedia\DoctrineEventConverterBundle\Model\Delayed;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DelayableEventDispatcher
{
    /**
     * @var array<int, list<Delayed>>
     */
    private array $delayed = [];

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ObjectIdCache $objectIdCache,
        private readonly ManagerRegistry $registry
    ) {
    }

    /**
     * @param AbstractEntityEvent<EntityInterface> $event
     */
    public function dispatch(
        AbstractEntityEvent $event
    ): void {
        $this->eventDispatcher->dispatch($event);
        $this->eventDispatcher->dispatch(new DispatchEvent($event));
    }

    public function delay(
        Delayed $delayed,
        int $depth
    ): void {
        $this->delayed[$depth][] = $delayed;
    }

    public function submitDelayed(
        int $depth
    ): void {
        foreach ($this->delayed[$depth] ?? [] as $delayed) {
            $event = $delayed->event;
            $manager = $this->registry->getManagerForClass($delayed->class);

            assert(null !== $manager);

            $id = $delayed->id ?? $this->objectIdCache->get($delayed->objectSplHash);

            assert(null !== $id);

            $entity = $manager->find($delayed->class, $id);

            assert($entity instanceof EntityInterface);

            $event->setEntity($entity);

            $this->eventDispatcher->dispatch($event);
            $this->eventDispatcher->dispatch(new DispatchEvent($event));
        }

        $this->delayed[$depth] = [];
    }

    public function clear(
        int $depth
    ): void {
        $this->delayed[$depth] = [];
    }
}
