<?php

namespace DualMedia\DoctrineEventConverterBundle\Storage;

use DualMedia\DoctrineEventConverterBundle\Event\AbstractEntityEvent;
use DualMedia\DoctrineEventConverterBundle\Interface\EntityInterface;
use DualMedia\DoctrineEventConverterBundle\Model\SubEvent;

/**
 * Responsible for storing and returning possible sub-events for entities.
 */
class SubEventService
{
    /**
     * @var array<class-string<EntityInterface>, array<class-string<AbstractEntityEvent>, non-empty-list<SubEvent>>>
     */
    private array $events = [];

    /**
     * @param list<array{
     *     0: class-string<AbstractEntityEvent>,
     *     1: non-empty-list<class-string<EntityInterface>>,
     *     2: bool,
     *     3: array<string, null|array{0?: mixed, 1?: mixed}>,
     *     4: array<string, mixed>,
     *     5: list<string>,
     *     6: bool
     * }> $entries
     */
    public function __construct(
        array $entries,
    ) {
        foreach ($entries as $entry) {
            [$eventClass, $entities, $allMode, $fieldList, $requirements, $types, $afterFlush] = $entry;

            foreach ($entities as $entity) {
                if (!array_key_exists($entity, $this->events)) {
                    $this->events[$entity] = [];
                }

                if (!array_key_exists($eventClass, $this->events[$entity])) {
                    $this->events[$entity][$eventClass] = []; // @phpstan-ignore-line
                }

                $this->events[$entity][$eventClass][] = new SubEvent($allMode, $fieldList, $requirements, $types, $afterFlush);
            }
        }
    }

    /**
     * @return array<class-string<AbstractEntityEvent>, non-empty-list<SubEvent>>
     */
    public function get(
        string $class,
    ): array {
        return $this->events[$class] ?? [];
    }
}
