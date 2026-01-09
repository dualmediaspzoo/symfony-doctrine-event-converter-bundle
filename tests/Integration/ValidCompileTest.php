<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests\Integration;

use Doctrine\ORM\Events;
use DualMedia\DoctrineEventConverterBundle\DoctrineEventConverterBundle;
use DualMedia\DoctrineEventConverterBundle\Model\Event;
use DualMedia\DoctrineEventConverterBundle\Storage\EventService;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Entity\ComplexEntity;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Entity\Item;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Event\ComplexEntityEvent;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Event\ItemEvent;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Event\SomeOtherEvent;
use DualMedia\DoctrineEventConverterBundle\Tests\KernelTestCase;
use DualMedia\DoctrineEventConverterProxy\DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Event\ComplexEntityPostUpdateEvent;
use DualMedia\DoctrineEventConverterProxy\DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Event\ComplexEntityPrePersistEvent;
use DualMedia\DoctrineEventConverterProxy\DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Event\ComplexEntityStatusChangedEvent;
use DualMedia\DoctrineEventConverterProxy\DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Event\ComplexEntityStatusChangedPrePersistEvent;
use DualMedia\DoctrineEventConverterProxy\DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Event\ItemPostPersistEvent;
use DualMedia\DoctrineEventConverterProxy\DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Event\ItemPostRemoveEvent;
use DualMedia\DoctrineEventConverterProxy\DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Event\ItemPostUpdateEvent;
use DualMedia\DoctrineEventConverterProxy\DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Event\ItemPrePersistEvent;
use DualMedia\DoctrineEventConverterProxy\DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Event\ItemPreRemoveEvent;
use DualMedia\DoctrineEventConverterProxy\DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Event\ItemPreUpdateEvent;
use PHPUnit\Framework\Attributes\Depends;
use Symfony\Component\Finder\Finder;

class ValidCompileTest extends KernelTestCase
{
    public function testGeneration(): void
    {
        static::assertFileExists(
            $this->getProxyClassPath(ItemEvent::class, Events::prePersist),
            'PrePersist Event should have been generated'
        );
        static::assertFileExists(
            $this->getProxyClassPath(ItemEvent::class, Events::postPersist),
            'PostPersist Event should have been generated'
        );
        static::assertFileExists(
            $this->getProxyClassPath(ItemEvent::class, Events::preUpdate),
            'PreUpdate Event should have been generated'
        );
        static::assertFileExists(
            $this->getProxyClassPath(ItemEvent::class, Events::postUpdate),
            'PostUpdate Event should have been generated'
        );
        static::assertFileExists(
            $this->getProxyClassPath(ItemEvent::class, Events::preRemove),
            'PreUpdate Event should have been generated'
        );
        static::assertFileExists(
            $this->getProxyClassPath(ItemEvent::class, Events::postRemove),
            'PreUpdate Event should have been generated'
        );

        /** @see SomeOtherEvent */
        $finder = new Finder();
        $finder
            ->in($this->getContainer()->getParameter('kernel.cache_dir').DIRECTORY_SEPARATOR.DoctrineEventConverterBundle::CACHE_DIRECTORY)
            ->name('*SomeOther*Event*.php')
            ->files();

        static::assertEquals(0, $finder->count(), 'No events should be created for SomeOtherEvent');

        static::assertFileExists(
            $this->getProxyClassPath(ComplexEntityEvent::class, ComplexEntityEvent::STATUS_CHANGED),
            ComplexEntityEvent::STATUS_CHANGED.' Event should have been generated'
        );
        static::assertFileExists(
            $this->getProxyClassPath(ComplexEntityEvent::class, Events::postUpdate),
            'PostUpdate Event should have been generated implicitly'
        );
        static::assertFileExists(
            $this->getProxyClassPath(ComplexEntityEvent::class, ComplexEntityEvent::STATUS_CHANGED_PRE_PERSIST),
            ComplexEntityEvent::STATUS_CHANGED_PRE_PERSIST.' Event should have been generated'
        );
        static::assertFileExists(
            $this->getProxyClassPath(ComplexEntityEvent::class, Events::prePersist),
            'PrePersist Event should have been generated implicitly'
        );
    }

    #[Depends('testGeneration')]
    public function testAutoload(): void
    {
        // ItemEvent
        static::assertTrue(class_exists(ItemPrePersistEvent::class));
        static::assertTrue(class_exists(ItemPostPersistEvent::class));
        static::assertTrue(class_exists(ItemPreUpdateEvent::class));
        static::assertTrue(class_exists(ItemPostUpdateEvent::class));
        static::assertTrue(class_exists(ItemPreRemoveEvent::class));
        static::assertTrue(class_exists(ItemPostRemoveEvent::class));

        // ComplexEntityEvent
        static::assertTrue(class_exists(ComplexEntityPrePersistEvent::class));
        static::assertTrue(class_exists(ComplexEntityPostUpdateEvent::class));
        static::assertTrue(class_exists(ComplexEntityStatusChangedEvent::class));
        static::assertTrue(class_exists(ComplexEntityStatusChangedPrePersistEvent::class));
    }

    #[Depends('testGeneration')]
    public function testCorrectContainerDefinitions(): void
    {
        $service = static::getContainer()->get(EventService::class);
        /** @var EventService $service */
        $list = [
            Events::postPersist => [
                Item::class => new Event(ItemPostPersistEvent::class),
            ],
            Events::postUpdate => [
                Item::class => new Event(ItemPostUpdateEvent::class),
                ComplexEntity::class => new Event(ComplexEntityPostUpdateEvent::class),
            ],
            Events::postRemove => [
                Item::class => new Event(ItemPostRemoveEvent::class),
            ],
            Events::prePersist => [
                Item::class => new Event(ItemPrePersistEvent::class),
                ComplexEntity::class => new Event(ComplexEntityPrePersistEvent::class),
            ],
            Events::preUpdate => [
                Item::class => new Event(ItemPreUpdateEvent::class),
            ],
            Events::preRemove => [
                Item::class => new Event(ItemPreRemoveEvent::class),
            ],
        ];

        foreach ($list as $event => $entityList) {
            foreach ($entityList as $entity => $events) {
                static::assertEquals(
                    $events,
                    $service->get($event, $entity)
                );
            }
        }
    }
}
