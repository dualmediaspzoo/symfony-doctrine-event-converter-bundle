<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests\DependencyInjection;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use DualMedia\DoctrineEventConverterBundle\DependencyInjection\CompilerPass\EventDetectionCompilerPass;
use DualMedia\DoctrineEventConverterBundle\DoctrineEventConverterBundle;
use DualMedia\DoctrineEventConverterBundle\Event\AbstractEntityEvent;
use DualMedia\DoctrineEventConverterBundle\EventSubscriber\DispatchingSubscriber;
use DualMedia\DoctrineEventConverterBundle\Exception\DependencyInjection\AbstractEntityEventNotExtendedException;
use DualMedia\DoctrineEventConverterBundle\Exception\DependencyInjection\EntityInterfaceMissingException;
use DualMedia\DoctrineEventConverterBundle\Exception\DependencyInjection\NoValidEntityFoundException;
use DualMedia\DoctrineEventConverterBundle\Exception\DependencyInjection\SubEventNameCollisionException;
use DualMedia\DoctrineEventConverterBundle\Exception\DependencyInjection\SubEventRequiredFieldsException;
use DualMedia\DoctrineEventConverterBundle\Exception\DependencyInjection\TargetClassFinalException;
use DualMedia\DoctrineEventConverterBundle\Exception\DependencyInjection\UnknownEventTypeException;
use DualMedia\DoctrineEventConverterBundle\Interfaces\EntityInterface;
use DualMedia\DoctrineEventConverterBundle\Proxy\Generator;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Entity\InvalidEntity;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Error\FinalClass\TestEvent as FinalClass;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Error\InvalidBaseEntity\TestEvent as InvalidBaseEntity;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Error\NotExtendingAbstractEntityEvent\TestEvent as NotExtendingAbstractEntityEvent;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Error\NoValidEntity\TestEvent as NoValidEntity;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Error\SubEventNameCollision\TestEvent as SubEventNameCollision;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Error\SubEventRequiredFields\TestEvent as SubEventRequiredFields;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Error\UnknownEventType\TestEvent as UnknownEventType;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * This test must not modify setup, as that's later tested for checking if the compiler pass will work without services.
 */
class CompilerPassTest extends AbstractCompilerPassTestCase
{
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
            'invalid',
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
            'ExistingName',
        ]));

        $this->compile();
    }

    public function testSubEventRequiredFieldsException(): void
    {
        $this->setDINamespace('SubEventRequiredFields');
        $this->loadRequiredServices();

        $this->expectException(SubEventRequiredFieldsException::class);
        $this->expectExceptionMessage(SubEventRequiredFieldsException::formatMessage([
            'SomeName',
            SubEventRequiredFields::class,
        ]));

        $this->compile();
    }

    protected function registerCompilerPass(
        ContainerBuilder $container
    ): void {
        $container->addCompilerPass(new EventDetectionCompilerPass());
    }

    private function loadRequiredServices(): void
    {
        $this->container->setParameter('kernel.cache_dir', $cache = '/'.self::getAbsolutePath(__DIR__.'/../../var/cache/test'));
        $this->setDefinition(Reader::class, new Definition(AnnotationReader::class));
        $this->setDefinition(Generator::class, new Definition(Generator::class, [
            $cache.'/'.DoctrineEventConverterBundle::CACHE_DIRECTORY,
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
            DoctrineEventConverterBundle::CONFIGURATION_ROOT.'.parent_namespace',
            'DualMedia\\DoctrineEventConverterBundle\\Tests\\Fixtures\\Error\\'.$namespace
        );
        $this->setParameter(
            DoctrineEventConverterBundle::CONFIGURATION_ROOT.'.parent_directory',
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
