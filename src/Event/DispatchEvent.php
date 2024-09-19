<?php

namespace DualMedia\DoctrineEventConverterBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Dispatched after an event has been dispatched.
 *
 * Useful if you really want to listen to all different kinds of events, but you don't want to listen to a ton of separate events
 */
final class DispatchEvent extends Event
{
    public function __construct(
        private readonly AbstractEntityEvent $event,
    ) {
    }

    public function getEvent(): AbstractEntityEvent
    {
        return $this->event;
    }
}
