<?php

namespace DM\DoctrineEventDistributorBundle\Tests\DependencyInjection;

use DM\DoctrineEventDistributorBundle\DependencyInjection\CompilerPass\EventDetectionCompilerPass;
use DM\DoctrineEventDistributorBundle\Event\AbstractEntityEvent;
use DM\DoctrineEventDistributorBundle\EventDistributorBundle;
use DM\DoctrineEventDistributorBundle\EventSubscriber\DispatchingSubscriber;
use DM\DoctrineEventDistributorBundle\Exception\DependencyInjection\AbstractEntityEventNotExtendedException;
use DM\DoctrineEventDistributorBundle\Exception\DependencyInjection\EntityInterfaceMissingException;
use DM\DoctrineEventDistributorBundle\Exception\DependencyInjection\NoValidEntityFoundException;
use DM\DoctrineEventDistributorBundle\Exception\DependencyInjection\SubEventLabelMissingException;
use DM\DoctrineEventDistributorBundle\Exception\DependencyInjection\SubEventNameCollisionException;
use DM\DoctrineEventDistributorBundle\Exception\DependencyInjection\SubEventRequiredFieldsException;
use DM\DoctrineEventDistributorBundle\Exception\DependencyInjection\TargetClassFinalException;
use DM\DoctrineEventDistributorBundle\Exception\DependencyInjection\UnknownEventTypeException;
use DM\DoctrineEventDistributorBundle\Interfaces\EntityInterface;
use DM\DoctrineEventDistributorBundle\Proxy\Generator;
use DM\DoctrineEventDistributorBundle\Tests\Fixtures\Entity\InvalidEntity;
use DM\DoctrineEventDistributorBundle\Tests\Fixtures\Entity\Item;
use DM\DoctrineEventDistributorBundle\Tests\Fixtures\Error\FinalClass\TestEvent as FinalClass;
use DM\DoctrineEventDistributorBundle\Tests\Fixtures\Error\InvalidBaseEntity\TestEvent as InvalidBaseEntity;
use DM\DoctrineEventDistributorBundle\Tests\Fixtures\Error\NoSubEventLabel\TestEvent as NoSubEventLabel;
use DM\DoctrineEventDistributorBundle\Tests\Fixtures\Error\NotExtendingAbstractEntityEvent\TestEvent as NotExtendingAbstractEntityEvent;
use DM\DoctrineEventDistributorBundle\Tests\Fixtures\Error\NoValidEntity\TestEvent as NoValidEntity;
use DM\DoctrineEventDistributorBundle\Tests\Fixtures\Error\SubEventNameCollision\TestEvent as SubEventNameCollision;
use DM\DoctrineEventDistributorBundle\Tests\Fixtures\Error\SubEventRequiredFields\TestEvent as SubEventRequiredFields;
use DM\DoctrineEventDistributorBundle\Tests\Fixtures\Error\UnknownEventType\TestEvent as UnknownEventType;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * This test must not modify setup, as that's later tested for checking if the compiler pass will work without services
 */
class CompilerPassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(
        ContainerBuilder $container
    ): void {
        $container->addCompilerPass(new EventDetectionCompilerPass());
    }

    public function testInvalidBaseEntity(): void
    {
        $this->setDINamespace('InvalidBaseEntity');
        $this->loadRequiredServices();

        $this->expectException(EntityInterfaceMissingException::class);
        $this->expectExceptionMessage(EntityInterfaceMissingException::formatMessage([
            InvalidEntity::class,
            EntityInterface::class,
            InvalidBaseEntity::class,
        ]));

        $this->compile();
    }

    public function testNotExtendingAbstractEntityEvent(): void
    {
        $this->setDINamespace('NotExtendingAbstractEntityEvent');
        $this->loadRequiredServices();

        $this->expectException(AbstractEntityEventNotExtendedException::class);
        $this->expectExceptionMessage(AbstractEntityEventNotExtendedException::formatMessage([
            NotExtendingAbstractEntityEvent::class,
            AbstractEntityEvent::class,
        ]));

        $this->compile();
    }

    public function testNoValidEntity(): void
    {
        $this->setDINamespace('NoValidEntity');
        $this->loadRequiredServices();

        $this->expectException(NoValidEntityFoundException::class);
        $this->expectExceptionMessage(NoValidEntityFoundException::formatMessage([
            NoValidEntity::class,
        ]));

        $this->compile();
    }

    public function testNoSubEventLabel(): void
    {
        $this->setDINamespace('NoSubEventLabel');
        $this->loadRequiredServices();

        $this->expectException(SubEventLabelMissingException::class);
        $this->expectExceptionMessage(SubEventLabelMissingException::formatMessage([
            NoSubEventLabel::class,
            Item::class,
        ]));

        $this->compile();
    }

    public function testFinalClass(): void
    {
        $this->setDINamespace('FinalClass');
        $this->loadRequiredServices();

        $this->expectException(TargetClassFinalException::class);
        $this->expectExceptionMessage(TargetClassFinalException::formatMessage([
            FinalClass::class,
        ]));

        $this->compile();
    }

    public function testUnknownEventType(): void
    {
        $this->setDINamespace('UnknownEventType');
        $this->loadRequiredServices();

        $this->expectException(UnknownEventTypeException::class);
        $this->expectExceptionMessage(UnknownEventTypeException::formatMessage([
            "invalid",
            UnknownEventType::class,
        ]));

        $this->compile();
    }

    public function testSubEventNameCollision(): void
    {
        $this->setDINamespace('SubEventNameCollision');
        $this->loadRequiredServices();

        $this->expectException(SubEventNameCollisionException::class);
        $this->expectExceptionMessage(SubEventNameCollisionException::formatMessage([
            SubEventNameCollision::class,
            "ExistingName",
        ]));

        $this->compile();
    }

    public function testSubEventRequiredFieldsException(): void
    {
        $this->setDINamespace('SubEventRequiredFields');
        $this->loadRequiredServices();

        $this->expectException(SubEventRequiredFieldsException::class);
        $this->expectExceptionMessage(SubEventRequiredFieldsException::formatMessage([
            "SomeName",
            SubEventRequiredFields::class,
        ]));

        $this->compile();
    }

    private function loadRequiredServices(): void
    {
        $this->container->setParameter('kernel.cache_dir', $cache = '/'.self::getAbsolutePath(__DIR__.'/../../var/cache/test'));
        $this->setDefinition(Reader::class, new Definition(AnnotationReader::class));
        $this->setDefinition(Generator::class, new Definition(Generator::class, [
            $cache.'/'.EventDistributorBundle::CACHE_DIRECTORY,
        ]));
        $this->setDefinition('event_dispatcher', new Definition(EventDispatcher::class));
        $this->setDefinition(DispatchingSubscriber::class, new Definition(DispatchingSubscriber::class, [
            new Reference('event_dispatcher'),
        ]));
    }

    private function setDINamespace(
        string $namespace
    ): void {
        $this->setParameter(
            'event_distributor.parent_namespace',
            'DM\\DoctrineEventDistributorBundle\\Tests\\Fixtures\\Error\\'.$namespace
        );
        $this->setParameter(
            'event_distributor.parent_directory',
            '/'.self::getAbsolutePath(__DIR__.'/../Fixtures/Error/'.$namespace)
        );
    }

    private static function getAbsolutePath(
        string $path
    ): string {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = [];
        foreach ($parts as $part) {
            if ('.' == $part) {
                continue;
            }
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return implode(DIRECTORY_SEPARATOR, $absolutes);
    }
}
