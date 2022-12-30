<?php

namespace DualMedia\DoctrineEventDistributorBundle\Tests\Fixtures\Error\SubEventRequiredFields;

use DualMedia\DoctrineEventDistributorBundle\Attributes\SubEvent;
use DualMedia\DoctrineEventDistributorBundle\Event\AbstractEntityEvent;
use DualMedia\DoctrineEventDistributorBundle\Tests\Fixtures\Entity\Item;
use JetBrains\PhpStorm\Pure;

/**
 * @extends AbstractEntityEvent<Item>
 */
#[SubEvent("SomeName")]
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
