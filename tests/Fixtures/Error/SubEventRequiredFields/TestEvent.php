<?php

namespace DM\DoctrineEventDistributorBundle\Tests\Fixtures\Error\SubEventRequiredFields;

use DM\DoctrineEventDistributorBundle\Attributes\SubEvent;
use DM\DoctrineEventDistributorBundle\Event\AbstractEntityEvent;
use DM\DoctrineEventDistributorBundle\Tests\Fixtures\Entity\Item;
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
