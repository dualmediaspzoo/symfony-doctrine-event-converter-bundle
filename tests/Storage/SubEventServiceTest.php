<?php

declare(strict_types=1);

namespace DualMedia\DoctrineEventConverterBundle\Tests\Storage;

use DualMedia\DoctrineEventConverterBundle\Model\SubEvent;
use DualMedia\DoctrineEventConverterBundle\Storage\SubEventService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
#[Group('storage')]
#[CoversClass(SubEventService::class)]
class SubEventServiceTest extends TestCase
{
    public function testEmpty(): void
    {
        static::assertEmpty(
            (new SubEventService([]))->get('class')
        );
    }

    public function testList(): void
    {
        $service = new SubEventService([
            'class' => [
                $event1 = $this->createMock(SubEvent::class),
                $event2 = $this->createMock(SubEvent::class),
            ],
        ]);

        static::assertSame([
            $event1,
            $event2
        ], $service->get('class'));
    }
}
