<?php

namespace DualMedia\DoctrineEventDistributorBundle\Tests\Fixtures\Error\FinalClass;

use DualMedia\DoctrineEventDistributorBundle\Attributes\PostPersistEvent;
use DualMedia\DoctrineEventDistributorBundle\Event\AbstractEntityEvent;
use DualMedia\DoctrineEventDistributorBundle\Tests\Fixtures\Entity\Item;
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
