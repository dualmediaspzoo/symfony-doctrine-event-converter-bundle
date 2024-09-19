<?php

namespace DualMedia\DoctrineEventConverterBundle\Service;

use DualMedia\DoctrineEventConverterBundle\Event\AbstractEntityEvent;
use DualMedia\DoctrineEventConverterBundle\Event\DispatchEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DelayableEventDispatcher
{
    /**
     * @var list<AbstractEntityEvent>
     */
    private array $eventsToDispatchAfterFlush = [];
    private bool $dispatchingDelayed = false;

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function dispatch(
        AbstractEntityEvent $event,
        bool $delay = false
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

    public function clearEvents(): void
    {
        $this->eventsToDispatchAfterFlush = [];
    }
}
