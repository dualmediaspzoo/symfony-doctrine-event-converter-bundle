<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Error\InvalidBaseEntity;

use DualMedia\DoctrineEventConverterBundle\Attribute\EventEntity;
use DualMedia\DoctrineEventConverterBundle\Attribute\PreUpdateEvent;
use DualMedia\DoctrineEventConverterBundle\Event\AbstractEntityEvent;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Entity\InvalidEntity;

#[EventEntity(InvalidEntity::class)]
#[PreUpdateEvent]
class TestEvent extends AbstractEntityEvent
{
}
