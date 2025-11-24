<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests\Service;

use Doctrine\ORM\Events;
use DualMedia\DoctrineEventConverterBundle\Service\SubEventService;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Entity\ComplexEntity;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Entity\Item;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Event\ComplexEntityEvent;
use PHPUnit\Framework\TestCase;
use Pkly\ServiceMockHelperTrait;

class SubEventServiceTest extends TestCase
{
    use ServiceMockHelperTrait;

    private SubEventService $service;

    protected function setUp(): void
    {
        $this->service = $this->createRealMockedServiceInstance(SubEventService::class, [
            'entries' => [
                [
                    ComplexEntityEvent::class,
                    [ComplexEntity::class],
                    false,
                    [
                        'stuff' => null,
                    ],
                    [
                        'requirement' => 42,
                    ],
                    [
                        Events::prePersist,
                    ],
                    true,
                ],
            ],
        ]);
    }

    public function test(): void
    {
        static::assertNotEmpty(
            $events = $this->service->get(ComplexEntity::class),
            'There should be exactly 1 event for specified inputs'
        );
        static::assertCount(1, $events, 'There should be exactly 1 event for specified inputs');

        static::assertArrayHasKey(
            ComplexEntityEvent::class,
            $events
        );
        static::assertCount(1, $events[ComplexEntityEvent::class]);
        $event = $events[ComplexEntityEvent::class][0];

        static::assertFalse(
            $event->allMode
        );
        static::assertEquals([
            'stuff' => null,
        ], $event->fields);
        static::assertEquals([
            'requirement' => 42,
        ], $event->requirements);
        static::assertEquals([
            Events::prePersist,
        ], $event->types);
        static::assertTrue(
            $event->afterFlush
        );
    }

    public function testEmpty(): void
    {
        static::assertEmpty(
            $this->service->get(Item::class),
            'There should be sub events for specified entity'
        );
    }
}
