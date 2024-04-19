<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests\Service;

use DualMedia\DoctrineEventConverterBundle\Service\EventService;
use PHPUnit\Framework\TestCase;
use Pkly\ServiceMockHelperTrait;

class EventServiceTest extends TestCase
{
    use ServiceMockHelperTrait;

    protected function setUp(): void
    {
        $this->service = $this->createRealMockedServiceInstance(EventService::class);
    }

    public function test(): void
    {
        $this->assertTrue(true);
    }
}
