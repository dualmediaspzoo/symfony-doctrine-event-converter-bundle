<?php

namespace DualMedia\DoctrineEventConverterBundle\Event;

use DualMedia\Common\Interface\IdentifiableInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Dispatched after an event has been dispatched.
 *
 * Useful if you really want to listen to all different kinds of events, but you don't want to listen to a ton of separate events
 */
final class DispatchEvent extends Event
{
    /**
     * @param AbstractEntityEvent<IdentifiableInterface> $event
     */
    public function __construct(
        private readonly AbstractEntityEvent $event,
    ) {
    }

    /**
     * @return AbstractEntityEvent<IdentifiableInterface>
     */
    public function getEvent(): AbstractEntityEvent
    {
        return $this->event;
    }
}
