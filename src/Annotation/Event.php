<?php

namespace DM\DoctrineEventDistributorBundle\Annotation;

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
     * @var mixed (string|string[])
     * @psalm-var string|non-empty-list<string>|null
     */
    public $entity = null;

    /**
     * @Enum({Events::prePersist, Events::postPersist, Events::preUpdate, Events::postUpdate, Events::preRemove, Events::postRemove})
     */
    protected string $type = Events::postPersist;

    public function getType(): string
    {
        return $this->type;
    }
}
