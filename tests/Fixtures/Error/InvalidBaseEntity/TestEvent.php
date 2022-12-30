<?php

namespace DualMedia\DoctrineEventDistributorBundle\Tests\Fixtures\Error\InvalidBaseEntity;

use DualMedia\DoctrineEventDistributorBundle\Attributes\PreUpdateEvent;
use DualMedia\DoctrineEventDistributorBundle\Event\AbstractEntityEvent;
use DualMedia\DoctrineEventDistributorBundle\Tests\Fixtures\Entity\InvalidEntity;
use JetBrains\PhpStorm\Pure;

#[PreUpdateEvent]
class TestEvent extends AbstractEntityEvent
{
    /**
     * @psalm-pure
     */
    #[Pure]
    public static function getEntityClass(): string|null
    {
        return InvalidEntity::class;
    }
}
