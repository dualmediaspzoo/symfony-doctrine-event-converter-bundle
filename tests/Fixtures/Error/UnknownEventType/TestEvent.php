<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Error\UnknownEventType;

use DualMedia\DoctrineEventConverterBundle\Attributes\SubEvent;
use DualMedia\DoctrineEventConverterBundle\Event\AbstractEntityEvent;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Entity\Item;

/**
 * @extends AbstractEntityEvent<Item>
 */
#[SubEvent('SomeEvent', types: ['invalid'])]
class TestEvent extends AbstractEntityEvent
{
    public static function getEntityClass(): string|null
    {
        return Item::class;
    }
}
