<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Error\SubEventNameCollision;

use DualMedia\DoctrineEventConverterBundle\Attributes\SubEvent;
use DualMedia\DoctrineEventConverterBundle\Event\AbstractEntityEvent;
use DualMedia\DoctrineEventConverterBundle\Model\Change;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Entity\Item;

/**
 * @extends AbstractEntityEvent<Item>
 */
#[SubEvent('ExistingName', changes: [new Change('status')])]
#[SubEvent('ExistingName', changes: [new Change('status')])]
class TestEvent extends AbstractEntityEvent
{
    public static function getEntityClass(): string|null
    {
        return Item::class;
    }
}
