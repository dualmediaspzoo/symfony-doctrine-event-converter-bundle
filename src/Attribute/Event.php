<?php

namespace DualMedia\DoctrineEventConverterBundle\Attribute;

use Doctrine\ORM\Events;
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
abstract readonly class Event
{
    /**
     * @see Events
     */
    public const string EVENT_TYPE = Events::postPersist;

    public function __construct(
        public bool $afterFlush = false,
    ) {
    }
}
