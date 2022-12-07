<?php

namespace DM\DoctrineEventDistributorBundle\Tests\Fixtures\Error\FinalClass;

use DM\DoctrineEventDistributorBundle\Annotation\PostPersistEvent;
use DM\DoctrineEventDistributorBundle\Event\AbstractEntityEvent;
use DM\DoctrineEventDistributorBundle\Tests\Fixtures\Entity\Item;

/**
 * @PostPersistEvent()
 */
final class TestEvent extends AbstractEntityEvent
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
