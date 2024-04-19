<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Event;

use DualMedia\DoctrineEventConverterBundle\Attributes\PostPersistEvent;
use DualMedia\DoctrineEventConverterBundle\Attributes\PostRemoveEvent;
use DualMedia\DoctrineEventConverterBundle\Attributes\PostUpdateEvent;
use DualMedia\DoctrineEventConverterBundle\Attributes\PrePersistEvent;
use DualMedia\DoctrineEventConverterBundle\Attributes\PreRemoveEvent;
use DualMedia\DoctrineEventConverterBundle\Attributes\PreUpdateEvent;
use DualMedia\DoctrineEventConverterBundle\Event\AbstractEntityEvent;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Entity\Item;

/**
 * @extends AbstractEntityEvent<Item>
 */
#[PrePersistEvent]
#[PostPersistEvent]
#[PreUpdateEvent]
#[PostUpdateEvent]
#[PreRemoveEvent]
#[PostRemoveEvent]
abstract class ItemEvent extends AbstractEntityEvent
{
    public static function getEntityClass(): string|null
    {
        return Item::class;
    }
}
