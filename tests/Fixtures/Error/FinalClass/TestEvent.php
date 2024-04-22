<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Error\FinalClass;

use DualMedia\DoctrineEventConverterBundle\Attributes\PostPersistEvent;
use DualMedia\DoctrineEventConverterBundle\Event\AbstractEntityEvent;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Entity\Item;

/**
 * @extends AbstractEntityEvent<Item>
 */
#[PostPersistEvent]
final class TestEvent extends AbstractEntityEvent
{
    public static function getEntityClass(): string|null
    {
        return Item::class;
    }
}
