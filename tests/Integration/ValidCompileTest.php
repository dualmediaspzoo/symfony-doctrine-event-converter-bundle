<?php

namespace DM\DoctrineEventDistributorBundle\Tests\Integration;

use DM\DoctrineEventDistributorBundle\DoctrineEventConverterBundle;
use DM\DoctrineEventDistributorBundle\EventSubscriber\DispatchingSubscriber;
use DM\DoctrineEventDistributorBundle\Tests\Fixtures\Entity\ComplexEntity;
use DM\DoctrineEventDistributorBundle\Tests\Fixtures\Entity\Item;
use DM\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ComplexEntityEvent;
use DM\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ItemEvent;
use DM\DoctrineEventDistributorBundle\Tests\Fixtures\Event\SomeOtherEvent;
use DM\DoctrineEventDistributorBundle\Tests\KernelTestCase;
use DM\DoctrineEventDistributorProxy\DM\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ComplexEntityPostUpdateEvent;
use DM\DoctrineEventDistributorProxy\DM\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ComplexEntityPrePersistEvent;
use DM\DoctrineEventDistributorProxy\DM\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ComplexEntityStatusChangedEvent;
use DM\DoctrineEventDistributorProxy\DM\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ComplexEntityStatusChangedPrePersistEvent;
use DM\DoctrineEventDistributorProxy\DM\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ItemPostPersistEvent;
use DM\DoctrineEventDistributorProxy\DM\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ItemPostRemoveEvent;
use DM\DoctrineEventDistributorProxy\DM\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ItemPostUpdateEvent;
use DM\DoctrineEventDistributorProxy\DM\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ItemPrePersistEvent;
use DM\DoctrineEventDistributorProxy\DM\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ItemPreRemoveEvent;
use DM\DoctrineEventDistributorProxy\DM\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ItemPreUpdateEvent;
use Doctrine\ORM\Events;
use Symfony\Component\Finder\Finder;

class ValidCompileTest extends KernelTestCase
{
    public function testGeneration()
    {
        $this->assertFileExists(
            $this->getProxyClassPath(ItemEvent::class, Events::prePersist),
            'PrePersist Event should have been generated'
        );
        $this->assertFileExists(
            $this->getProxyClassPath(ItemEvent::class, Events::postPersist),
            'PostPersist Event should have been generated'
        );
        $this->assertFileExists(
            $this->getProxyClassPath(ItemEvent::class, Events::preUpdate),
            'PreUpdate Event should have been generated'
        );
        $this->assertFileExists(
            $this->getProxyClassPath(ItemEvent::class, Events::postUpdate),
            'PostUpdate Event should have been generated'
        );
        $this->assertFileExists(
            $this->getProxyClassPath(ItemEvent::class, Events::preRemove),
            'PreUpdate Event should have been generated'
        );
        $this->assertFileExists(
            $this->getProxyClassPath(ItemEvent::class, Events::postRemove),
            'PreUpdate Event should have been generated'
        );

        /** @see SomeOtherEvent */
        $finder = new Finder();
        $finder
            ->in(static::$container->getParameter('kernel.cache_dir').DIRECTORY_SEPARATOR.DoctrineEventConverterBundle::CACHE_DIRECTORY)
            ->name('*SomeOther*Event*.php')
            ->files();

        $this->assertEquals(0, $finder->count(), 'No events should be created for SomeOtherEvent');

        $this->assertFileExists(
            $this->getProxyClassPath(ComplexEntityEvent::class, ComplexEntityEvent::STATUS_CHANGED),
            ComplexEntityEvent::STATUS_CHANGED." Event should have been generated"
        );
        $this->assertFileExists(
            $this->getProxyClassPath(ComplexEntityEvent::class, Events::postUpdate),
            "PostUpdate Event should have been generated implicitly"
        );
        $this->assertFileExists(
            $this->getProxyClassPath(ComplexEntityEvent::class, ComplexEntityEvent::STATUS_CHANGED_PRE_PERSIST),
            ComplexEntityEvent::STATUS_CHANGED_PRE_PERSIST." Event should have been generated"
        );
        $this->assertFileExists(
            $this->getProxyClassPath(ComplexEntityEvent::class, Events::prePersist),
            "PrePersist Event should have been generated implicitly"
        );
    }

    /**
     * @depends testGeneration
     */
    public function testAutoload()
    {
        // ItemEvent
        $this->assertTrue(class_exists(ItemPrePersistEvent::class));
        $this->assertTrue(class_exists(ItemPostPersistEvent::class));
        $this->assertTrue(class_exists(ItemPreUpdateEvent::class));
        $this->assertTrue(class_exists(ItemPostUpdateEvent::class));
        $this->assertTrue(class_exists(ItemPreRemoveEvent::class));
        $this->assertTrue(class_exists(ItemPostRemoveEvent::class));

        // ComplexEntityEvent
        $this->assertTrue(class_exists(ComplexEntityPrePersistEvent::class));
        $this->assertTrue(class_exists(ComplexEntityPostUpdateEvent::class));
        $this->assertTrue(class_exists(ComplexEntityStatusChangedEvent::class));
        $this->assertTrue(class_exists(ComplexEntityStatusChangedPrePersistEvent::class));
    }

    /**
     * @depends testGeneration
     */
    public function testCorrectContainerDefinitions()
    {
        $subscriber = self::$container->get(DispatchingSubscriber::class);

        $list = [
            Events::postPersist => [
                Item::class => [
                    ItemPostPersistEvent::class,
                ],
            ],
            Events::postUpdate => [
                Item::class => [
                    ItemPostUpdateEvent::class,
                ],
                ComplexEntity::class => [
                    ComplexEntityPostUpdateEvent::class,
                ],
            ],
            Events::postRemove => [
                Item::class => [
                    ItemPostRemoveEvent::class,
                ],
            ],
            Events::prePersist => [
                Item::class => [
                    ItemPrePersistEvent::class,
                ],
                ComplexEntity::class => [
                    ComplexEntityPrePersistEvent::class,
                ],
            ],
            Events::preUpdate => [
                Item::class => [
                    ItemPreUpdateEvent::class,
                ],
            ],
            Events::preRemove => [
                Item::class => [
                    ItemPreRemoveEvent::class,
                ],
            ],
        ];

        foreach ($list as $event => $entityList) {
            foreach ($entityList as $entity => $events) {
                $this->checkArrayWithoutOrderImportance(
                    $events,
                    $subscriber->getEvents($event, $entity)
                );
            }
        }
    }

    private function checkArrayWithoutOrderImportance(
        array $expected,
        array $actual
    ): void {
        sort($expected);
        sort($actual);

        $this->assertSame($expected, $actual);
    }
}
