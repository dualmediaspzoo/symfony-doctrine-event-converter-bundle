<?php

namespace DualMedia\DoctrineEventConverterBundle\Service;

use Doctrine\ORM\Events;
use DualMedia\DoctrineEventConverterBundle\Event\AbstractEntityEvent;
use DualMedia\DoctrineEventConverterBundle\Interface\EntityInterface;
use DualMedia\DoctrineEventConverterBundle\Model\Event;

/**
 * Responsible for storing and returning possible events for entities during Doctrine changes.
 */
class EventService
{
    /**
     * List of events to be dispatched after entity changes.
     *
     * @var non-empty-array<string, array<class-string<EntityInterface>, list<Event>>>
     */
    private array $events = [
        Events::postPersist => [], Events::postUpdate => [], Events::postRemove => [],
        Events::prePersist => [], Events::preUpdate => [], Events::preRemove => [],
    ];

    /**
     * @param list<array{
     *     0: class-string<AbstractEntityEvent>,
     *     1: non-empty-list<class-string<EntityInterface>>,
     *     2: string,
     *     3: bool
     * }> $entries list of events to be later used by the service
     */
    public function __construct(
        array $entries,
    ) {
        foreach ($entries as $entry) {
            [$eventClass, $entities, $event, $afterFlush] = $entry;

            if (!array_key_exists($event, $this->events)) {
                continue;
            }

            foreach ($entities as $entityClass) {
                if (!array_key_exists($entityClass, $this->events[$event])) {
                    $this->events[$event][$entityClass] = [];
                }

                $this->events[$event][$entityClass][] = new Event($eventClass, $afterFlush);
            }
        }
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
        return $this->events[$event][$class] ?? [];
    }
}
