<?php

namespace DualMedia\DoctrineEventDistributorBundle\Tests\Fixtures\Error\SubEventNameCollision;

use DualMedia\DoctrineEventDistributorBundle\Attributes\SubEvent;
use DualMedia\DoctrineEventDistributorBundle\Event\AbstractEntityEvent;
use DualMedia\DoctrineEventDistributorBundle\Tests\Fixtures\Entity\Item;
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
