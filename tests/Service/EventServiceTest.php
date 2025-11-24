<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests\Service;

use Doctrine\ORM\Events;
use DualMedia\DoctrineEventConverterBundle\Service\EventService;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Entity\ComplexEntity;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Entity\Item;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Event\ComplexEntityEvent;
use PHPUnit\Framework\TestCase;
use Pkly\ServiceMockHelperTrait;

class EventServiceTest extends TestCase
{
    use ServiceMockHelperTrait;

    private EventService $service;

    protected function setUp(): void
    {
        $this->service = $this->createRealMockedServiceInstance(EventService::class, [
            'entries' => [
                [
                    ComplexEntityEvent::class,
                    [ComplexEntity::class],
                    Events::prePersist,
                    true,
                ],
            ],
        ]);
    }

    public function test(): void
    {
        static::assertNotEmpty(
            $events = $this->service->get(Events::prePersist, ComplexEntity::class),
            'There should be exactly 1 event for specified inputs'
        );
        static::assertCount(1, $events, 'There should be exactly 1 event for specified inputs');

        static::assertEquals(
            ComplexEntityEvent::class,
            $events[0]->eventClass
        );
        static::assertTrue($events[0]->afterFlush);
    }

    public function testNotFound(): void
    {
        static::assertEmpty(
            $this->service->get(Events::postRemove, Item::class),
            'No events should be returned from service'
        );
    }
}
