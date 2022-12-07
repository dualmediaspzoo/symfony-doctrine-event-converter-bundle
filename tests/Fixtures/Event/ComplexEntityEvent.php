<?php

namespace DM\DoctrineEventDistributorBundle\Tests\Fixtures\Event;

use DM\DoctrineEventDistributorBundle\Annotation\SubEvent;
use DM\DoctrineEventDistributorBundle\Event\AbstractEntityEvent;
use DM\DoctrineEventDistributorBundle\Tests\Fixtures\Entity\ComplexEntity;
use Doctrine\ORM\Events;

/**
 * This class is used for testing SubEvent and Event (implicit) generation
 *
 * @SubEvent(ComplexEntityEvent::STATUS_CHANGED, fields="status")
 * @SubEvent(ComplexEntityEvent::STATUS_CHANGED_PRE_PERSIST, fields="status", types={Events::prePersist})
 * @SubEvent(ComplexEntityEvent::STATUS_WITH_REQUIREMENTS, fields="status", requirements={"unimportant"="specific"})
 * @SubEvent(ComplexEntityEvent::STATUS_CHANGED_15, fields={"status"={15}})
 * @SubEvent(ComplexEntityEvent::STATUS_CHANGED_FROM_10_TO_15, fields={"status"={10, 15}})
 */
abstract class ComplexEntityEvent extends AbstractEntityEvent
{
    public const STATUS_CHANGED = "StatusChanged";
    public const STATUS_CHANGED_PRE_PERSIST = "StatusChangedPrePersist";
    public const STATUS_WITH_REQUIREMENTS = "StatusWithUnimportantRequirements";
    public const STATUS_CHANGED_15 = "StatusChangedTo15";
    public const STATUS_CHANGED_FROM_10_TO_15 = "StatusChangedFrom10To15";

    /**
     * @return string|null
     * @psalm-pure
     */
    public static function getEntityClass(): ?string
    {
        return ComplexEntity::class;
    }
}
