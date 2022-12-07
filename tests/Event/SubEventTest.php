<?php

namespace DM\DoctrineEventDistributorBundle\Tests\Event;

use DM\DoctrineEventDistributorBundle\Event\DispatchEvent;
use DM\DoctrineEventDistributorBundle\Interfaces\MainEventInterface;
use DM\DoctrineEventDistributorBundle\Tests\Fixtures\Entity\ComplexEntity;
use DM\DoctrineEventDistributorBundle\Tests\KernelTestCase;
use DM\DoctrineEventDistributorProxy\DM\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ComplexEntityPostUpdateEvent;
use DM\DoctrineEventDistributorProxy\DM\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ComplexEntityPrePersistEvent;
use DM\DoctrineEventDistributorProxy\DM\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ComplexEntityStatusChangedEvent;
use DM\DoctrineEventDistributorProxy\DM\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ComplexEntityStatusChangedFrom10To15Event;
use DM\DoctrineEventDistributorProxy\DM\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ComplexEntityStatusChangedPrePersistEvent;
use DM\DoctrineEventDistributorProxy\DM\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ComplexEntityStatusChangedTo15Event;
use DM\DoctrineEventDistributorProxy\DM\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ComplexEntityStatusWithUnimportantRequirementsEvent;

class SubEventTest extends KernelTestCase
{
    public const EVENT_LIST = [
        ComplexEntityStatusChangedEvent::class,
        ComplexEntityStatusChangedPrePersistEvent::class,
        ComplexEntityStatusWithUnimportantRequirementsEvent::class,
        ComplexEntityStatusChangedTo15Event::class,
        ComplexEntityStatusChangedFrom10To15Event::class,
        DispatchEvent::class,
    ];

    public function testNoEvent(): void
    {
        $events = [];

        $this->addMappedListeners(
            $events,
            self::EVENT_LIST
        );

        /** @var ComplexEntity $entity */
        $entity = $this->getComplexRepo()->find(1);
        $entity->setUnimportant("new");

        $this->getManager()->persist($entity);
        $this->getManager()->flush();

        $this->assertEmpty(
            array_filter(
                $events,
                fn (DispatchEvent $o) => !($o->getEvent() instanceof MainEventInterface)
            )
        );
    }

    public function testStatusChangeEvent(): void
    {
        $events = [];

        $this->addMappedListeners(
            $events,
            [
                ComplexEntityPostUpdateEvent::class,
                ...self::EVENT_LIST,
            ]
        );

        /** @var ComplexEntity $entity */
        $entity = $this->getComplexRepo()->find(1);
        $entity->setStatus(255);

        $this->getManager()->persist($entity);
        $this->getManager()->flush();

        $this->assertEntityEventList(
            $events,
            [
                ComplexEntityPostUpdateEvent::class,
                DispatchEvent::class,
                ComplexEntityStatusChangedEvent::class,
                DispatchEvent::class,
            ],
            $entity
        );
    }

    public function testStatusPrePersistEvent(): void
    {
        $events = [];

        $this->addMappedListeners(
            $events,
            [
                ComplexEntityPrePersistEvent::class,
                ...self::EVENT_LIST,
            ]
        );

        $entity = new ComplexEntity();
        $entity->setStatus(1)
            ->setName("Whatever")
            ->setUnimportant("something");

        $this->getManager()->persist($entity);
        $this->getManager()->flush();

        $this->assertEntityEventList(
            $events,
            [
                ComplexEntityPrePersistEvent::class,
                DispatchEvent::class,
                ComplexEntityStatusChangedPrePersistEvent::class,
                DispatchEvent::class,
            ],
            $entity
        );
    }

    public function testStatusWithRequirements(): void
    {
        $events = [];

        $this->addMappedListeners(
            $events,
            [
                ComplexEntityPostUpdateEvent::class,
                ...self::EVENT_LIST,
            ]
        );

        /** @var ComplexEntity $entity */
        $entity = $this->getComplexRepo()->find(1);
        $entity->setUnimportant("specific")
            ->setStatus(16);

        $this->getManager()->persist($entity);
        $this->getManager()->flush();

        $this->assertEntityEventList(
            $events,
            [
                ComplexEntityPostUpdateEvent::class,
                DispatchEvent::class,
                ComplexEntityStatusChangedEvent::class,
                DispatchEvent::class,
                ComplexEntityStatusWithUnimportantRequirementsEvent::class,
                DispatchEvent::class,
            ],
            $entity
        );
    }

    public function testStatusSpecificChanges(): void
    {
        $events = [];

        $this->addMappedListeners(
            $events,
            [
                ComplexEntityPostUpdateEvent::class,
                ...self::EVENT_LIST,
            ]
        );

        /** @var ComplexEntity $entity */
        $entity = $this->getComplexRepo()->find(1);
        $entity->setStatus(15);

        $this->getManager()->persist($entity);
        $this->getManager()->flush();

        $this->assertEntityEventList(
            $events,
            [
                ComplexEntityPostUpdateEvent::class,
                DispatchEvent::class,
                ComplexEntityStatusChangedEvent::class,
                DispatchEvent::class,
                ComplexEntityStatusChangedTo15Event::class,
                DispatchEvent::class,
            ],
            $entity
        );

        $this->clearListeners();

        $entity->setStatus(10);

        // this isn't super important, so we're not checking
        $this->getManager()->persist($entity);
        $this->getManager()->flush();

        $events = [];

        $this->assertEquals(10, $entity->getStatus(), 'Status right now should be 10');

        $this->addMappedListeners(
            $events,
            [
                ComplexEntityPostUpdateEvent::class,
                ...self::EVENT_LIST,
            ]
        );

        $entity->setStatus(15);

        $this->getManager()->persist($entity);
        $this->getManager()->flush();

        $this->assertEntityEventList(
            $events,
            [
                ComplexEntityPostUpdateEvent::class,
                DispatchEvent::class,
                ComplexEntityStatusChangedEvent::class,
                DispatchEvent::class,
                ComplexEntityStatusChangedTo15Event::class,
                DispatchEvent::class,
                ComplexEntityStatusChangedFrom10To15Event::class,
                DispatchEvent::class,
            ],
            $entity
        );
    }
}
