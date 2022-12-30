<?php

namespace DualMedia\DoctrineEventDistributorBundle\Tests\Fixtures\Event;

use DualMedia\DoctrineEventDistributorBundle\Attributes\PostPersistEvent;
use DualMedia\DoctrineEventDistributorBundle\Attributes\PostRemoveEvent;
use DualMedia\DoctrineEventDistributorBundle\Attributes\PostUpdateEvent;
use DualMedia\DoctrineEventDistributorBundle\Attributes\PrePersistEvent;
use DualMedia\DoctrineEventDistributorBundle\Attributes\PreRemoveEvent;
use DualMedia\DoctrineEventDistributorBundle\Attributes\PreUpdateEvent;
use DualMedia\DoctrineEventDistributorBundle\Event\AbstractEntityEvent;
use DualMedia\DoctrineEventDistributorBundle\Tests\Fixtures\Entity\Item;
use JetBrains\PhpStorm\Pure;

/**
 * @extends AbstractEntityEvent<Item>
 */
#[PrePersistEvent]
#[PostPersistEvent]
#[PreUpdateEvent]
#[PostUpdateEvent]
#[PreRemoveEvent]
#[PostRemoveEvent]
abstract class ItemEvent extends AbstractEntityEvent
{
    /**
     * @psalm-pure
     */
    #[Pure]
    public static function getEntityClass(): string|null
    {
        return Item::class;
    }
}
