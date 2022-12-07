<?php

namespace DM\DoctrineEventDistributorBundle\Tests\Fixtures\Error\SubEventNameCollision;

use DM\DoctrineEventDistributorBundle\Annotation\SubEvent;
use DM\DoctrineEventDistributorBundle\Event\AbstractEntityEvent;
use DM\DoctrineEventDistributorBundle\Tests\Fixtures\Entity\Item;

/**
 * @SubEvent("ExistingName", fields="status")
 * @SubEvent("ExistingName", fields="status")
 */
class TestEvent extends AbstractEntityEvent
{
    /**
     * @return string|null
     * @psalm-pure
     */
    public static function getEntityClass(): ?string
    {
        return Item::class;
    }
}
