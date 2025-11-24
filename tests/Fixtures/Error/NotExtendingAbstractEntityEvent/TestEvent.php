<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Error\NotExtendingAbstractEntityEvent;

use DualMedia\DoctrineEventConverterBundle\Attribute\EventEntity;
use DualMedia\DoctrineEventConverterBundle\Attribute\PostPersistEvent;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Entity\Item;

#[EventEntity(Item::class)]
#[PostPersistEvent]
class TestEvent
{
}
