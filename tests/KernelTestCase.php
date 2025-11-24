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
        string $eventType,
    ): string {
        return $this->getContainer()->get(Generator::class)->resolveFilePath(Generator::getProxyFqcn($class, $eventType)); // @phpstan-ignore-line
    }

    protected function getItemRepo(): EntityRepository
    {
        return $this->getContainer()->get('doctrine')->getManager()->getRepository(Item::class); // @phpstan-ignore-line
    }

    protected function getComplexRepo(): EntityRepository
    {
        return $this->getContainer()->get('doctrine')->getManager()->getRepository(ComplexEntity::class); // @phpstan-ignore-line
    }

    protected function getManager(): ObjectManager
    {
        return $this->getContainer()->get('doctrine')->getManager(); // @phpstan-ignore-line
    }

    protected function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->getContainer()->get('event_dispatcher'); // @phpstan-ignore-line
    }

    /**
     * @param DispatchEvent[]|AbstractEntityEvent[] $events
     */
    protected function assertEntityEventList(
        array $events,
        array $expected,
        $entity,
    ): void {
        $classes = array_map(
            fn ($o) => get_class($o),
            $events
        );

        static::assertEquals($expected, $classes);

        for ($i = 0; $i < count($events); $i++) {
            if ($events[$i] instanceof DispatchEvent) {
                static::assertSame(
                    $events[$i - 1],
                    $events[$i]->getEvent()
                );
                static::assertSame(
                    $entity,
                    $events[$i]->getEvent()->getEntity()
                );
            } elseif ($events[$i] instanceof AbstractEntityEvent) {
                static::assertSame(
                    $entity,
                    $events[$i]->getEntity()
                );
            }
        }
    }

    protected function addMappedListeners(
        array &$out,
        array $events,
    ): void {
        foreach ($events as $event) {
            if (!array_key_exists($event, $this->listeners)) {
                $this->listeners[$event] = [];
            }

            $this->listeners[$event][] = $this->getSimpleCallable($out);
            $this->getEventDispatcher()->addListener(
                $event,
                $this->listeners[$event][count($this->listeners[$event]) - 1]
            );
        }
    }

    protected function clearListeners(): void
    {
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
        array &$events,
    ): callable {
        return function ($e) use (&$events) {
            $events[] = $e;
        };
    }
}
