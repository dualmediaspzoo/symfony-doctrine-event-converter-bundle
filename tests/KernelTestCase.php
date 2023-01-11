<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests;

use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectManager;
use DualMedia\DoctrineEventConverterBundle\Event\AbstractEntityEvent;
use DualMedia\DoctrineEventConverterBundle\Event\DispatchEvent;
use DualMedia\DoctrineEventConverterBundle\Proxy\Generator;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Entity\ComplexEntity;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Entity\Item;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as SymfonyKernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class KernelTestCase extends SymfonyKernelTestCase
{
    protected array $listeners = [];

    protected function setUp(): void
    {
        static::bootKernel();
    }

    protected function getProxyClassPath(
        string $class,
        string $eventType
    ): string {
        return $this->getContainer()->get(Generator::class)->resolveFilePath(Generator::getProxyFqcn($class, $eventType));
    }

    protected function getItemRepo(): EntityRepository
    {
        return $this->getContainer()->get('doctrine')->getManager()->getRepository(Item::class);
    }

    protected function getComplexRepo(): EntityRepository
    {
        return $this->getContainer()->get('doctrine')->getManager()->getRepository(ComplexEntity::class);
    }

    protected function getManager(): ObjectManager
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    protected function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->getContainer()->get('event_dispatcher');
    }

    /**
     * @param DispatchEvent[]|AbstractEntityEvent[] $events
     * @param array $expected
     * @param $entity
     */
    protected function assertEntityEventList(
        array $events,
        array $expected,
        $entity
    ): void {
        $classes = array_map(
            fn ($o) => get_class($o),
            $events
        );

        $this->assertEquals($expected, $classes);

        for ($i = 0; $i < count($events); $i++) {
            if ($events[$i] instanceof DispatchEvent) {
                $this->assertSame(
                    $events[$i-1],
                    $events[$i]->getEvent()
                );
                $this->assertSame(
                    $entity,
                    $events[$i]->getEvent()->getEntity()
                );
            } elseif ($events[$i] instanceof AbstractEntityEvent) {
                $this->assertSame(
                    $entity,
                    $events[$i]->getEntity()
                );
            }
        }
    }

    protected function addMappedListeners(
        array &$out,
        array $events
    ): void {
        foreach ($events as $event) {
            if (!array_key_exists($event, $this->listeners)) {
                $this->listeners[$event] = [];
            }

            $this->listeners[$event][] = $this->getSimpleCallable($out);
            $this->getEventDispatcher()->addListener(
                $event,
                $this->listeners[$event][count($this->listeners[$event])-1]
            );
        }
    }

    protected function clearListeners(): void
    {
        if (null === $this->getEventDispatcher()) {
            return;
        }

        foreach ($this->listeners as $event => $listeners) {
            foreach ($listeners as $listener) {
                $this->getEventDispatcher()->removeListener(
                    $event,
                    $listener
                );
            }
        }

        $this->listeners = [];
    }

    protected function getSimpleCallable(
        array &$events
    ): callable {
        return function ($e) use (&$events) {
            $events[] = $e;
        };
    }
}
