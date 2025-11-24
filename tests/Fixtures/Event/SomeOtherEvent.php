<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Event;

use DualMedia\DoctrineEventConverterBundle\Attribute\EventEntity;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Entity\Item;
use Symfony\Contracts\EventDispatcher\Event;

#[EventEntity(Item::class)]
class SomeOtherEvent extends Event
{
}
