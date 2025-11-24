<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Event;

use DualMedia\DoctrineEventConverterBundle\Attribute\EventEntity;
use DualMedia\DoctrineEventConverterBundle\Attribute\PostPersistEvent;
use DualMedia\DoctrineEventConverterBundle\Attribute\PostRemoveEvent;
use DualMedia\DoctrineEventConverterBundle\Attribute\PostUpdateEvent;
use DualMedia\DoctrineEventConverterBundle\Attribute\PrePersistEvent;
use DualMedia\DoctrineEventConverterBundle\Attribute\PreRemoveEvent;
use DualMedia\DoctrineEventConverterBundle\Attribute\PreUpdateEvent;
use DualMedia\DoctrineEventConverterBundle\Event\AbstractEntityEvent;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Entity\Item;

/**
 * @extends AbstractEntityEvent<Item>
 */
#[EventEntity(Item::class)]
#[PrePersistEvent]
#[PostPersistEvent]
#[PreUpdateEvent]
#[PostUpdateEvent]
#[PreRemoveEvent]
#[PostRemoveEvent]
abstract class ItemEvent extends AbstractEntityEvent
{
}
