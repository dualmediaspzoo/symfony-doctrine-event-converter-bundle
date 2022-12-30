<?php

namespace DM\DoctrineEventDistributorBundle\Tests\Fixtures\Error\FinalClass;

use DM\DoctrineEventDistributorBundle\Attributes\PostPersistEvent;
use DM\DoctrineEventDistributorBundle\Event\AbstractEntityEvent;
use DM\DoctrineEventDistributorBundle\Tests\Fixtures\Entity\Item;
use JetBrains\PhpStorm\Pure;

/**
 * @extends AbstractEntityEvent<Item>
 */
#[PostPersistEvent]
final class TestEvent extends AbstractEntityEvent
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
