<?php

namespace DM\DoctrineEventDistributorBundle\Tests\Fixtures\Event;

use DM\DoctrineEventDistributorBundle\Annotation\PostPersistEvent;
use DM\DoctrineEventDistributorBundle\Annotation\PostRemoveEvent;
use DM\DoctrineEventDistributorBundle\Annotation\PostUpdateEvent;
use DM\DoctrineEventDistributorBundle\Annotation\PrePersistEvent;
use DM\DoctrineEventDistributorBundle\Annotation\PreRemoveEvent;
use DM\DoctrineEventDistributorBundle\Annotation\PreUpdateEvent;
use DM\DoctrineEventDistributorBundle\Event\AbstractEntityEvent;
use DM\DoctrineEventDistributorBundle\Tests\Fixtures\Entity\Item;

/**
 * @PrePersistEvent()
 * @PostPersistEvent()
 * @PreUpdateEvent()
 * @PostUpdateEvent()
 * @PreRemoveEvent()
 * @PostRemoveEvent()
 */
abstract class ItemEvent extends AbstractEntityEvent
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
