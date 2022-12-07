<?php

namespace DM\DoctrineEventDistributorBundle\Tests\Fixtures\Error\InvalidBaseEntity;

use DM\DoctrineEventDistributorBundle\Annotation\PreUpdateEvent;
use DM\DoctrineEventDistributorBundle\Event\AbstractEntityEvent;
use DM\DoctrineEventDistributorBundle\Tests\Fixtures\Entity\InvalidEntity;

/**
 * @PreUpdateEvent()
 */
class TestEvent extends AbstractEntityEvent
{
    /**
     * @return string|null
     * @psalm-pure
     */
    public static function getEntityClass(): ?string
    {
        return InvalidEntity::class;
    }
}
