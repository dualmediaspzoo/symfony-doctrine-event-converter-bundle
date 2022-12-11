<?php

namespace DM\DoctrineEventDistributorBundle\Tests\Fixtures\Error\InvalidBaseEntity;

use DM\DoctrineEventDistributorBundle\Attributes\PreUpdateEvent;
use DM\DoctrineEventDistributorBundle\Event\AbstractEntityEvent;
use DM\DoctrineEventDistributorBundle\Tests\Fixtures\Entity\InvalidEntity;
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
