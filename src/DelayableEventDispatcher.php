<?php

namespace DualMedia\DoctrineEventConverterBundle;

use DualMedia\DoctrineEventConverterBundle\Event\AbstractEntityEvent;
use DualMedia\DoctrineEventConverterBundle\Event\DispatchEvent;
use DualMedia\DoctrineEventConverterBundle\Interface\EntityInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DelayableEventDispatcher
{
    /**
     * @var list<AbstractEntityEvent<EntityInterface>>
     */
    private array $eventsToDispatchAfterFlush = [];
    private bool $dispatchingDelayed = false;

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @param AbstractEntityEvent<EntityInterface> $event
     */
    public function dispatch(
        AbstractEntityEvent $event,
        bool $delay = false,
    ): void {
        if ($delay) {
            $this->eventsToDispatchAfterFlush[] = $event;
        } else {
            $this->eventDispatcher->dispatch($event);
            $this->eventDispatcher->dispatch(new DispatchEvent($event));
        }
    }

    public function submitDelayed(): void
    {
        if ($this->dispatchingDelayed) {
            return; // prevent infinite loop with afterFlush events
        }

        $this->dispatchingDelayed = true;

        foreach ($this->eventsToDispatchAfterFlush as $event) {
            $this->eventDispatcher->dispatch($event);
            $this->eventDispatcher->dispatch(new DispatchEvent($event));
        }

        $this->dispatchingDelayed = false;
        $this->eventsToDispatchAfterFlush = [];
    }

    public function clear(): void
    {
        $this->eventsToDispatchAfterFlush = [];
    }
}
