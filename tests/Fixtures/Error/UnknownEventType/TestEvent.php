<?php

namespace DM\DoctrineEventDistributorBundle\Tests\Fixtures\Error\UnknownEventType;

use DM\DoctrineEventDistributorBundle\Attributes\SubEvent;
use DM\DoctrineEventDistributorBundle\Event\AbstractEntityEvent;
use DM\DoctrineEventDistributorBundle\Tests\Fixtures\Entity\Item;
use JetBrains\PhpStorm\Pure;

/**
 * @extends AbstractEntityEvent<Item>
 */
#[SubEvent("SomeEvent", types: ["invalid"])]
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
