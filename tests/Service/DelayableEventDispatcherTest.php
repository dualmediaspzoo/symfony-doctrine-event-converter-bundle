<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests\Service;

use DualMedia\DoctrineEventConverterBundle\Event\AbstractEntityEvent;
use DualMedia\DoctrineEventConverterBundle\Event\DispatchEvent;
use DualMedia\DoctrineEventConverterBundle\Service\DelayableEventDispatcher;
use DualMedia\DoctrineEventConverterBundle\Tests\KernelTestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DelayableEventDispatcherTest extends KernelTestCase
{
    public function testService()
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $dispatcher->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturnCallback(function ($arg) {
                if ($arg instanceof DispatchEvent) {
                    $this->assertSame($arg->getEvent()->getEntityId(), 123456);
                } else {
                    $this->assertSame($arg->getEntityId(), 123456);
                }

                return $arg;
            });

        $service = new DelayableEventDispatcher($dispatcher);

        $event = $this->createMock(AbstractEntityEvent::class);
        $event->method('getEntityId')->willReturn(123456);
        $service->dispatch($event);
    }

}
