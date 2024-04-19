<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests\Service;

use DualMedia\DoctrineEventConverterBundle\Service\EventService;
use PHPUnit\Framework\TestCase;
use Pkly\ServiceMockHelperTrait;

class EventServiceTest extends TestCase
{
    use ServiceMockHelperTrait;

    private EventService $service;

    protected function setUp(): void
    {
        $this->service = $this->createRealMockedServiceInstance(EventService::class, [
            'entries' => [],
        ]);
    }

    public function test(): void
    {
        $this->assertTrue(true);
    }
}
