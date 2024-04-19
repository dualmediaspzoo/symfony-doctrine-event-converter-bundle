<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Event;

use Doctrine\ORM\Events;
use DualMedia\DoctrineEventConverterBundle\Attributes\SubEvent;
use DualMedia\DoctrineEventConverterBundle\Event\AbstractEntityEvent;
use DualMedia\DoctrineEventConverterBundle\Model\Change;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Entity\ComplexEntity;
use JetBrains\PhpStorm\Pure;

/**
 * This class is used for testing SubEvent and Event (implicit) generation.
 *
 * @extends AbstractEntityEvent<ComplexEntity>
 */
#[SubEvent(ComplexEntityEvent::STATUS_CHANGED, changes: [new Change('status')])]
#[SubEvent(ComplexEntityEvent::STATUS_CHANGED_PRE_PERSIST, types: [Events::prePersist], changes: [new Change('status')])]
#[SubEvent(ComplexEntityEvent::STATUS_WITH_REQUIREMENTS, requirements: ['unimportant' => 'specific'], changes: [new Change('status')])]
#[SubEvent(ComplexEntityEvent::STATUS_CHANGED_15, changes: [new Change('status', to: 15)])]
#[SubEvent(ComplexEntityEvent::STATUS_CHANGED_FROM_10_TO_15, changes: [new Change('status', 10, 15)])]
abstract class ComplexEntityEvent extends AbstractEntityEvent
{
    public const STATUS_CHANGED = 'StatusChanged';
    public const STATUS_CHANGED_PRE_PERSIST = 'StatusChangedPrePersist';
    public const STATUS_WITH_REQUIREMENTS = 'StatusWithUnimportantRequirements';
    public const STATUS_CHANGED_15 = 'StatusChangedTo15';
    public const STATUS_CHANGED_FROM_10_TO_15 = 'StatusChangedFrom10To15';

    /**
     * @psalm-pure
     */
    #[Pure]
    public static function getEntityClass(): string|null
    {
        return ComplexEntity::class;
    }
}
