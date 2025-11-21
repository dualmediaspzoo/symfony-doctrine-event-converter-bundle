<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Error\UnknownEventType;

use DualMedia\DoctrineEventConverterBundle\Attribute\EventEntity;
use DualMedia\DoctrineEventConverterBundle\Attribute\SubEvent;
use DualMedia\DoctrineEventConverterBundle\Event\AbstractEntityEvent;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Entity\Item;

/**
 * @extends AbstractEntityEvent<Item>
 */
#[EventEntity(Item::class)]
#[SubEvent('SomeEvent', types: ['invalid'])]
class TestEvent extends AbstractEntityEvent
{
}
