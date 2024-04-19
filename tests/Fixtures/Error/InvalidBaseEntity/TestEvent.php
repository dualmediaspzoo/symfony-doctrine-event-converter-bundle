<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Error\InvalidBaseEntity;

use DualMedia\DoctrineEventConverterBundle\Attributes\PreUpdateEvent;
use DualMedia\DoctrineEventConverterBundle\Event\AbstractEntityEvent;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Entity\InvalidEntity;
use JetBrains\PhpStorm\Pure;

#[PreUpdateEvent]
class TestEvent extends AbstractEntityEvent
{
    public static function getEntityClass(): string|null
    {
        return InvalidEntity::class;
    }
}
