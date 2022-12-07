<?php

namespace DM\DoctrineEventDistributorBundle\Tests\Fixtures\Error\SubEventRequiredFields;

use DM\DoctrineEventDistributorBundle\Annotation\SubEvent;
use DM\DoctrineEventDistributorBundle\Event\AbstractEntityEvent;
use DM\DoctrineEventDistributorBundle\Tests\Fixtures\Entity\Item;

/**
 * @SubEvent("SomeName")
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
