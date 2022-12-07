<?php

namespace DM\DoctrineEventDistributorBundle\Tests\Event;

use DM\DoctrineEventDistributorBundle\Event\DispatchEvent;
use DM\DoctrineEventDistributorBundle\Tests\Fixtures\Entity\Item;
use DM\DoctrineEventDistributorBundle\Tests\KernelTestCase;
use DM\DoctrineEventDistributorProxy\DM\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ItemPostPersistEvent;
use DM\DoctrineEventDistributorProxy\DM\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ItemPostRemoveEvent;
use DM\DoctrineEventDistributorProxy\DM\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ItemPostUpdateEvent;
use DM\DoctrineEventDistributorProxy\DM\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ItemPrePersistEvent;
use DM\DoctrineEventDistributorProxy\DM\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ItemPreRemoveEvent;
use DM\DoctrineEventDistributorProxy\DM\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ItemPreUpdateEvent;

class DoctrineTest extends KernelTestCase
{
    public function testItemLoad(): void
    {
        $items = $this->getItemRepo()->findAll();
        $this->assertCount(1, $items);
    }

    public function testPersistEvents(): void
    {
        $events = [];

        $this->addMappedListeners(
            $events,
            [
                ItemPrePersistEvent::class,
                ItemPostPersistEvent::class,
                DispatchEvent::class,
            ]
        );

        $item = new Item();
        $item->setStatus(5);

        $this->getManager()->persist($item);
        $this->getManager()->flush();

        $this->assertEntityEventList(
            $events,
            [
                ItemPrePersistEvent::class,
                DispatchEvent::class,
                ItemPostPersistEvent::class,
                DispatchEvent::class,
            ],
            $item
        );
    }

    public function testUpdateEvents(): void
    {
        $events = [];

        $this->addMappedListeners(
            $events,
            [
                ItemPreUpdateEvent::class,
                ItemPostUpdateEvent::class,
                DispatchEvent::class,
            ]
        );

        /** @var Item $item */
        $item = $this->getItemRepo()->find(1);
        $item->setStatus(5);

        $this->getManager()->persist($item);
        $this->getManager()->flush();

        $this->assertEntityEventList(
            $events,
            [
                ItemPreUpdateEvent::class,
                DispatchEvent::class,
                ItemPostUpdateEvent::class,
                DispatchEvent::class,
            ],
            $item
        );
    }

    public function testRemoveEvents(): void
    {
        $events = [];

        $this->addMappedListeners(
            $events,
            [
                ItemPreRemoveEvent::class,
                ItemPostRemoveEvent::class,
                DispatchEvent::class,
            ]
        );

        /** @var Item $item */
        $item = $this->getItemRepo()->find(1);

        $this->getManager()->remove($item);
        $this->getManager()->flush();

        $this->assertEntityEventList(
            $events,
            [
                ItemPreRemoveEvent::class,
                DispatchEvent::class,
                ItemPostRemoveEvent::class,
                DispatchEvent::class,
            ],
            $item
        );
    }
}
