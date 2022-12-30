<?php

namespace DualMedia\DoctrineEventDistributorBundle\Tests\Fixtures\Error\UnknownEventType;

use DualMedia\DoctrineEventDistributorBundle\Attributes\SubEvent;
use DualMedia\DoctrineEventDistributorBundle\Event\AbstractEntityEvent;
use DualMedia\DoctrineEventDistributorBundle\Tests\Fixtures\Entity\Item;
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
