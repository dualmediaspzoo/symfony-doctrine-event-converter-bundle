<?php

namespace DualMedia\DoctrineEventConverterBundle\Attributes;

use Doctrine\ORM\Events;
use DualMedia\DoctrineEventConverterBundle\Event\AbstractEntityEvent;
use DualMedia\DoctrineEventConverterBundle\EventSubscriber\DispatchingSubscriber;

/**
 * Base class for main events, the other annotations should be used in your code.
 *
 * @see DispatchingSubscriber
 * @see PrePersistEvent
 * @see PostPersistEvent
 * @see PreUpdateEvent
 * @see PostUpdateEvent
 * @see PreRemoveEvent
 * @see PostRemoveEvent
 */
abstract class Event
{
    /**
     * This value is not required assuming you override the {@link AbstractEntityEvent::getEntityClass()} method.
     *
     * @var non-empty-list<class-string>|null
     */
    public readonly array|null $entity;

    /**
     * @see Events
     */
    protected string $type = Events::postPersist;

    /**
     * @param non-empty-list<class-string>|null $entity
     */
    public function __construct(
        array|null $entity = null,
        public readonly bool $afterFlush = false,
    ) {
        $this->entity = $entity;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
