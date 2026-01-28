<?php

namespace DualMedia\DoctrineEventConverterBundle\Storage;

use Doctrine\ORM\Events;
use DualMedia\Common\Interface\IdentifiableInterface;
use DualMedia\DoctrineEventConverterBundle\Model\Event;

/**
 * Responsible for storing and returning possible events for entities during Doctrine changes.
 */
class EventService
{
    /**
     * @param array<string, non-empty-array<class-string<IdentifiableInterface>, Event>> $events list of events, mapped with {@link Events}->class->Event model
     */
    public function __construct(
        private readonly array $events
    ) {
    }

    /**
     * @param class-string $class
     */
    public function get(
        string $event,
        string $class,
    ): Event|null {
        return $this->events[$event][$class] ?? null;
    }
}
