<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Error\FinalClass;

use DualMedia\DoctrineEventConverterBundle\Attribute\EventEntity;
use DualMedia\DoctrineEventConverterBundle\Attribute\PostPersistEvent;
use DualMedia\DoctrineEventConverterBundle\Event\AbstractEntityEvent;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Entity\Item;

/**
 * @extends AbstractEntityEvent<Item>
 */
#[EventEntity(Item::class)]
#[PostPersistEvent]
final class TestEvent extends AbstractEntityEvent
{
}
