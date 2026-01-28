<?php

namespace DualMedia\DoctrineEventConverterBundle\Storage;

use DualMedia\Common\Interface\IdentifiableInterface;
use DualMedia\DoctrineEventConverterBundle\Model\SubEvent;

/**
 * Responsible for storing and returning possible sub-events for entities.
 */
class SubEventService
{
    /**
     * @param array<class-string<IdentifiableInterface>, non-empty-list<SubEvent>> $events
     */
    public function __construct(
        private readonly array $events
    ) {
    }

    /**
     * @param class-string<IdentifiableInterface> $class
     *
     * @return list<SubEvent>
     */
    public function get(
        string $class,
    ): array {
        return $this->events[$class] ?? [];
    }
}
