<?php

namespace DM\DoctrineEventDistributorBundle\Tests\Fixtures\Error\SubEventNameCollision;

use DM\DoctrineEventDistributorBundle\Attributes\SubEvent;
use DM\DoctrineEventDistributorBundle\Event\AbstractEntityEvent;
use DM\DoctrineEventDistributorBundle\Tests\Fixtures\Entity\Item;
use JetBrains\PhpStorm\Pure;

/**
 * @extends AbstractEntityEvent<Item>
 */
#[SubEvent("ExistingName", fields: "status")]
#[SubEvent("ExistingName", fields: "status")]
class TestEvent extends AbstractEntityEvent
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
