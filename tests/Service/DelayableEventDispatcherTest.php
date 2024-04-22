<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests\Service;

use DualMedia\DoctrineEventConverterBundle\Event\AbstractEntityEvent;
use DualMedia\DoctrineEventConverterBundle\Event\DispatchEvent;
use DualMedia\DoctrineEventConverterBundle\Service\DelayableEventDispatcher;
use PHPUnit\Framework\TestCase;
use Pkly\ServiceMockHelperTrait;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DelayableEventDispatcherTest extends TestCase
{
    use ServiceMockHelperTrait;

    public function testService(): void
    {
        $service = $this->createRealMockedServiceInstance(DelayableEventDispatcher::class);

        $this->getMockedService(EventDispatcherInterface::class)
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturnCallback(function ($arg) {
                if ($arg instanceof DispatchEvent) {
                    $this->assertSame($arg->getEvent()->getEntityId(), 123456);
                } else {
                    $this->assertSame($arg->getEntityId(), 123456);
                }

                return $arg;
            });

        $event = $this->createMock(AbstractEntityEvent::class);
        $event->method('getEntityId')->willReturn(123456);
        $service->dispatch($event);
    }
}
