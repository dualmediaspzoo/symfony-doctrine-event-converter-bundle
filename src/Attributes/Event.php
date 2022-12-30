<?php

namespace DM\DoctrineEventDistributorBundle\Attributes;

use DM\DoctrineEventDistributorBundle\Event\AbstractEntityEvent;
use DM\DoctrineEventDistributorBundle\EventSubscriber\DispatchingSubscriber;
use Doctrine\ORM\Events;

/**
 * Base class for main events, the other annotations should be used in your code
 *
 * @see DispatchingSubscriber
 *
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
     * This value is not required assuming you override the {@link AbstractEntityEvent::getEntityClass()} method
     *
     * @var class-string|non-empty-list<class-string>|null
     */
    public string|array|null $entity = null;

    /**
     * @see Events
     */
    protected string $type = Events::postPersist;

    public function getType(): string
    {
        return $this->type;
    }
}
