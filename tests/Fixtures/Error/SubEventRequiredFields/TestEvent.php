<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Error\SubEventRequiredFields;

use DualMedia\DoctrineEventConverterBundle\Attribute\EventEntity;
use DualMedia\DoctrineEventConverterBundle\Attribute\SubEvent;
use DualMedia\DoctrineEventConverterBundle\Event\AbstractEntityEvent;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Entity\Item;

/**
 * @extends AbstractEntityEvent<Item>
 */
#[EventEntity(Item::class)]
#[SubEvent('SomeName')]
class TestEvent extends AbstractEntityEvent
{
}
