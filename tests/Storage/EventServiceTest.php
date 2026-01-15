<?php

declare(strict_types=1);

namespace DualMedia\DoctrineEventConverterBundle\Tests\Storage;

use Doctrine\ORM\Events;
use DualMedia\DoctrineEventConverterBundle\Model\Event;
use DualMedia\DoctrineEventConverterBundle\Storage\EventService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
#[Group('storage')]
#[CoversClass(EventService::class)]
class EventServiceTest extends TestCase
{
    public function testNothing(): void
    {
        static::assertNull(
            (new EventService([]))->get('event', 'class')
        );
    }

    public function testGeneric(): void
    {
        $service = new EventService([
            Events::postUpdate => [
                'class' => $event = $this->createMock(Event::class),
            ],
        ]);

        static::assertSame(
            $event,
            $service->get(Events::postUpdate, 'class')
        );
    }
}
