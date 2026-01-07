<?php

namespace DualMedia\DoctrineEventConverterBundle\Storage;

use Doctrine\ORM\Events;
use DualMedia\DoctrineEventConverterBundle\Interface\EntityInterface;
use DualMedia\DoctrineEventConverterBundle\Model\Event;

/**
 * Responsible for storing and returning possible events for entities during Doctrine changes.
 */
class EventService
{
    /**
     * @param array<string, non-empty-array<class-string<EntityInterface>, list<string>>> $mappedEvents list of events, mapped with {@link Events}->class->list<id> of models (in instances)
     * @param array<string, Event> $instances
     */
    public function __construct(
        private readonly array $mappedEvents,
        private readonly array $instances
    ) {
    }

    /**
     * @param class-string $class
     *
     * @return list<Event>
     */
    public function get(
        string $event,
        string $class,
    ): array {
        return array_map(
            fn (string $id) => $this->instances[$id],
            $this->mappedEvents[$event][$class] ?? []
        );
    }
}
