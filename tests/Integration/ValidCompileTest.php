<?php

namespace DualMedia\DoctrineEventDistributorBundle\Tests\Integration;

use Doctrine\ORM\Events;
use DualMedia\DoctrineEventDistributorBundle\DoctrineEventConverterBundle;
use DualMedia\DoctrineEventDistributorBundle\EventSubscriber\DispatchingSubscriber;
use DualMedia\DoctrineEventDistributorBundle\Tests\Fixtures\Entity\ComplexEntity;
use DualMedia\DoctrineEventDistributorBundle\Tests\Fixtures\Entity\Item;
use DualMedia\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ComplexEntityEvent;
use DualMedia\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ItemEvent;
use DualMedia\DoctrineEventDistributorBundle\Tests\Fixtures\Event\SomeOtherEvent;
use DualMedia\DoctrineEventDistributorBundle\Tests\KernelTestCase;
use DualMedia\DoctrineEventDistributorProxy\DualMedia\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ComplexEntityPostUpdateEvent;
use DualMedia\DoctrineEventDistributorProxy\DualMedia\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ComplexEntityPrePersistEvent;
use DualMedia\DoctrineEventDistributorProxy\DualMedia\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ComplexEntityStatusChangedEvent;
use DualMedia\DoctrineEventDistributorProxy\DualMedia\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ComplexEntityStatusChangedPrePersistEvent;
use DualMedia\DoctrineEventDistributorProxy\DualMedia\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ItemPostPersistEvent;
use DualMedia\DoctrineEventDistributorProxy\DualMedia\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ItemPostRemoveEvent;
use DualMedia\DoctrineEventDistributorProxy\DualMedia\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ItemPostUpdateEvent;
use DualMedia\DoctrineEventDistributorProxy\DualMedia\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ItemPrePersistEvent;
use DualMedia\DoctrineEventDistributorProxy\DualMedia\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ItemPreRemoveEvent;
use DualMedia\DoctrineEventDistributorProxy\DualMedia\DoctrineEventDistributorBundle\Tests\Fixtures\Event\ItemPreUpdateEvent;
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
            ->in($this->getContainer()->getParameter('kernel.cache_dir').DIRECTORY_SEPARATOR.DoctrineEventConverterBundle::CACHE_DIRECTORY)
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
        $subscriber = $this->getContainer()->get(DispatchingSubscriber::class);

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
