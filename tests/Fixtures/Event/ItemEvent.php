<?php

namespace DM\DoctrineEventDistributorBundle\Tests\Fixtures\Event;

use DM\DoctrineEventDistributorBundle\Attributes\PostPersistEvent;
use DM\DoctrineEventDistributorBundle\Attributes\PostRemoveEvent;
use DM\DoctrineEventDistributorBundle\Attributes\PostUpdateEvent;
use DM\DoctrineEventDistributorBundle\Attributes\PrePersistEvent;
use DM\DoctrineEventDistributorBundle\Attributes\PreRemoveEvent;
use DM\DoctrineEventDistributorBundle\Attributes\PreUpdateEvent;
use DM\DoctrineEventDistributorBundle\Event\AbstractEntityEvent;
use DM\DoctrineEventDistributorBundle\Tests\Fixtures\Entity\Item;
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
