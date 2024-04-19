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
        foreach ($this->eventsToDispatchAfterFlush as $event) {
            $this->eventDispatcher->dispatch($event);
            $this->eventDispatcher->dispatch(new DispatchEvent($event));
        }

        $this->eventsToDispatchAfterFlush = [];
    }

    public function clearEvents(): void
    {
        $this->eventsToDispatchAfterFlush = [];
    }
}
