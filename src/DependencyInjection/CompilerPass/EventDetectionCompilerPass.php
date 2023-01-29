<?php

namespace DualMedia\DoctrineEventConverterBundle\DependencyInjection\CompilerPass;

use Doctrine\ORM\Events;
use DualMedia\DoctrineEventConverterBundle\Attributes\Event;
use DualMedia\DoctrineEventConverterBundle\Attributes\PostPersistEvent;
use DualMedia\DoctrineEventConverterBundle\Attributes\PostRemoveEvent;
use DualMedia\DoctrineEventConverterBundle\Attributes\PostUpdateEvent;
use DualMedia\DoctrineEventConverterBundle\Attributes\PrePersistEvent;
use DualMedia\DoctrineEventConverterBundle\Attributes\PreRemoveEvent;
use DualMedia\DoctrineEventConverterBundle\Attributes\PreUpdateEvent;
use DualMedia\DoctrineEventConverterBundle\Attributes\SubEvent;
use DualMedia\DoctrineEventConverterBundle\DependencyInjection\Model\Undefined;
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
use DualMedia\DoctrineEventConverterBundle\Exception\Proxy\DirectoryNotWritable;
use DualMedia\DoctrineEventConverterBundle\Exception\Proxy\TargetClassFinalException as ProxyTargetClassFinalException;
use DualMedia\DoctrineEventConverterBundle\Exception\Proxy\TargetClassNamingSchemeInvalidException;
use DualMedia\DoctrineEventConverterBundle\Interfaces\EntityInterface;
use DualMedia\DoctrineEventConverterBundle\Interfaces\MainEventInterface;
use DualMedia\DoctrineEventConverterBundle\Interfaces\SubEventInterface;
use DualMedia\DoctrineEventConverterBundle\Proxy\Generator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;

class EventDetectionCompilerPass implements CompilerPassInterface
{
    /**
     * Array of maps of the doctrine events to something we can actually find quickly
     *
     * @var array<string, class-string<Event>>
     */
    public const DOCTRINE_TO_ANNOTATION_MAP = [
        Events::prePersist => PrePersistEvent::class,
        Events::postPersist => PostPersistEvent::class,
        Events::preUpdate => PreUpdateEvent::class,
        Events::postUpdate => PostUpdateEvent::class,
        Events::preRemove => PreRemoveEvent::class,
        Events::postRemove => PostRemoveEvent::class,
    ];

    /**
     * @param ContainerBuilder $container
     *
     * @throws AbstractEntityEventNotExtendedException
     * @throws TargetClassFinalException
     * @throws DirectoryNotWritable
     * @throws EntityInterfaceMissingException
     * @throws NoValidEntityFoundException
     * @throws UnknownEventTypeException
     * @throws ProxyTargetClassFinalException
     * @throws TargetClassNamingSchemeInvalidException
     * @throws SubEventNameCollisionException
     * @throws SubEventRequiredFieldsException
     * @throws \ReflectionException
     */
    public function process(
        ContainerBuilder $container
    ): void {
        if (!$container->hasDefinition(Generator::class) ||
            !$container->has(Generator::class) ||
            !$container->hasDefinition(DispatchingSubscriber::class)) {
            return;
        }

        /** @var Generator $generator */
        $generator = $container->get(Generator::class);
        $subscriber = $container->getDefinition(DispatchingSubscriber::class);

        /** @var array<class-string<AbstractEntityEvent>, non-empty-list<Event>> $events */
        $events = [];

        /** @var array<class-string<AbstractEntityEvent>, non-empty-list<SubEvent>> $subEvents */
        $subEvents = [];

        $finder = new Finder();

        /** @var string $path */
        $path = $container->getParameter(DoctrineEventConverterBundle::CONFIGURATION_ROOT.'.parent_directory');
        /** @var string $namespace */
        $namespace = $container->getParameter(DoctrineEventConverterBundle::CONFIGURATION_ROOT.'.parent_namespace');

        // attempt to expand paths
        $match = [];
        preg_match_all('/%(.+)%/', $path, $match);
        if (count($match[0] ?? [])) {
            foreach ($match[0] as $i => $item) {
                /** @var string $param */
                $param = $container->getParameter($match[1][$i]);
                $path = str_replace($item, $param, $path);
            }
        }

        /**
         * This just lets us see if some name will be taken at any point, since SubEvents are created from a single class, they could collide
         *
         * @var string[] $uniqueSubEventNames
         */
        $uniqueSubEventNames = [];

        $nonGlobPath = rtrim($path, "*\\/");
        foreach ($finder->files()->in($path)->name('*.php') as $file) {
            $class = $namespace.'\\'.str_replace(['.php', '/'], ['', '\\'], mb_substr($file->getRealPath(), mb_strpos($file->getRealPath(), $nonGlobPath) + mb_strlen($nonGlobPath) + 1));
            /** @var class-string $class */
            try {
                $reflection = new \ReflectionClass($class);
                /** @var list<Event|SubEvent> $attributes */
                $attributes = [];

                foreach ($reflection->getAttributes() as $attribute) {
                    $instance = $attribute->newInstance();

                    if ($instance instanceof Event || $instance instanceof SubEvent) {
                        $attributes[] = $instance;
                    }
                }
            } catch (\ReflectionException) {
                continue;
            }

            foreach ($attributes as $annotation) {
                if ($annotation instanceof Event) {
                    $this->validateEventReflection($class, $reflection);
                    $this->applyEntityClass($annotation, $reflection);

                    /** @var list<class-string> $entity */
                    $entity = $annotation->entity;
                    $this->validateEntityClass($entity, $class);

                    if (!array_key_exists($class, $events)) {
                        $events[$class] = [];
                    }

                    $events[$class][] = $annotation;
                } elseif ($annotation instanceof SubEvent) {
                    $this->validateEventReflection($class, $reflection);
                    $this->applyEntityClass($annotation, $reflection);

                    /** @var list<class-string> $entity */
                    $entity = $annotation->entity;
                    $this->validateEntityClass($entity, $class);
                    $this->updateSubEventAnnotationFields($annotation, $class);

                    if (!array_key_exists($class, $subEvents)) {
                        $subEvents[$class] = [];
                    }

                    $subEvents[$class][] = $annotation;

                    $uniq = $class.ucfirst($annotation->label);
                    if (in_array($uniq, $uniqueSubEventNames)) {
                        throw SubEventNameCollisionException::new([
                            $class,
                            $annotation->label,
                        ]);
                    }

                    $uniqueSubEventNames[] = $uniq;
                }
            }
        }

        $cacheDir = $container->getParameter('kernel.cache_dir').DIRECTORY_SEPARATOR.DoctrineEventConverterBundle::CACHE_DIRECTORY; // @phpstan-ignore-line
        if (!is_dir($cacheDir) && (false === @mkdir($cacheDir, 0775, true))) {
            throw DirectoryNotWritable::new([$cacheDir]);
        }

        if (!is_writable($cacheDir)) {
            throw DirectoryNotWritable::new([$cacheDir]);
        }

        $finder = new Finder();
        // clear old event files for regeneration
        foreach ($finder->files()->in($cacheDir)->name('*.php') as $file) {
            unlink($file->getRealPath());
        }

        // we're starting with sub events because those might need an implicit creation of main events
        /** @var class-string<AbstractEntityEvent> $class */
        foreach ($subEvents as $class => $annotations) {
            foreach ($annotations as $annotation) {
                /** @psalm-suppress InvalidArgument */
                if (!array_key_exists($class, $events)) {
                    $events[$class] = [];
                }

                foreach ($annotation->types as $type) {
                    $annotationClass = self::DOCTRINE_TO_ANNOTATION_MAP[$type]; // won't work otherwise lol php
                    $found = false;
                    for ($i = 0; $i < count($events[$class]); $i++) {
                        if ($events[$class][$i] instanceof $annotationClass) {
                            $found = true;
                            break;
                        }
                    }

                    if ($found) {
                        continue;
                    }

                    /** @var Event $missing */
                    $missing = new $annotationClass();
                    /**
                     * @psalm-suppress InvalidPropertyAssignmentValue
                     */
                    $missing->entity = $annotation->entity; // @phpstan-ignore-line
                    $events[$class][] = $missing;
                }

                // create and add sub events
                $out = $generator->generateProxyClass(
                    $class,
                    $annotation->label,
                    [SubEventInterface::class]
                );
                /** @see DispatchingSubscriber::registerSubEvent() */
                $subscriber->addMethodCall('registerSubEvent', [
                    $out,
                    $annotation->entity,
                    $annotation->allMode,
                    $annotation->fields,
                    $annotation->requirements,
                    $annotation->types,
                    $annotation->priority,
                ]);
            }
        }

        // create and add main events
        /** @var class-string<AbstractEntityEvent> $class */
        foreach ($events as $class => $annotations) {
            foreach ($annotations as $annotation) {
                $out = $generator->generateProxyClass(
                    $class,
                    $annotation->getType(),
                    [MainEventInterface::class]
                );
                /** @see DispatchingSubscriber::registerEvent() */
                $subscriber->addMethodCall('registerEvent', [
                    $out,
                    $annotation->entity,
                    $annotation->getType(),
                ]);
            }
        }
    }

    /**
     * @param string $class
     * @param \ReflectionClass $reflection
     *
     * @throws AbstractEntityEventNotExtendedException
     * @throws TargetClassFinalException
     */
    private function validateEventReflection(
        string $class,
        \ReflectionClass $reflection
    ): void {
        if ($reflection->isFinal()) {
            throw TargetClassFinalException::new([$reflection->getName()]);
        }

        if (!is_subclass_of($class, AbstractEntityEvent::class)) {
            throw AbstractEntityEventNotExtendedException::new([
                $class,
                AbstractEntityEvent::class,
            ]);
        }
    }

    /**
     * @param Event|SubEvent $annotation
     *
     * @throws NoValidEntityFoundException
     */
    private function applyEntityClass(
        SubEvent|Event $annotation,
        \ReflectionClass $reflection
    ): void {
        if (!is_array($annotation->entity)) {
            if (!mb_strlen($class = call_user_func($reflection->getName().'::getEntityClass') ?? '')) { // @phpstan-ignore-line
                throw NoValidEntityFoundException::new([$reflection->getName()]);
            }
            /** @var class-string $class */
            $annotation->entity = [$class];
        }
    }

    /**
     * @param list<class-string> $class
     *
     * @throws EntityInterfaceMissingException
     */
    private function validateEntityClass(
        array $class,
        string $eventClass
    ): void {
        foreach ($class as $cls) {
            if (is_subclass_of($cls, EntityInterface::class)) { // fits by inheritance
                return;
            }

            throw EntityInterfaceMissingException::new([$cls, EntityInterface::class, $eventClass]);
        }
    }

    /**
     * @param SubEvent $subEvent
     * @param string $class
     *
     * @throws UnknownEventTypeException
     * @throws SubEventRequiredFieldsException
     */
    private function updateSubEventAnnotationFields(
        SubEvent $subEvent,
        string $class
    ): void {
        if (empty($subEvent->types)) {
            $subEvent->types = [Events::postUpdate];
        }

        if (!empty($v = array_filter($subEvent->types, fn (string $s) => !array_key_exists($s, self::DOCTRINE_TO_ANNOTATION_MAP)))) {
            throw UnknownEventTypeException::new([
                implode(', ', $v),
                $class,
            ]);
        }

        // todo: update with a configuration model helper
        $subEvent->fields = is_array($subEvent->fields) ? $subEvent->fields : [$subEvent->fields];
        $out = [];

        foreach ($subEvent->fields as $possibleName => $possibleValues) {
            if (is_numeric($possibleName) && is_string($possibleValues)) {
                $out[$possibleValues] = null;
            } elseif (is_array($possibleValues)) {
                $out[$possibleName] = 2 === count($possibleValues) ? $possibleValues : [1 => $possibleValues[0]];
            }
        }

        if (!empty($out)) {
            trigger_deprecation(
                'dualmedia/symfony-doctrine-event-converter-bundle',
                '2.1.2',
                'Using "%s" is deprecated, move to using "%s" instead',
                'fields',
                'changes'
            );
        }

        foreach ($subEvent->changes as $change) {
            if ($change->from instanceof Undefined && $change->to instanceof Undefined) {
                $out[$change->name] = null;
            } else {
                // we must not modify the keys of the arrays
                $out[$change->name] =
                    ($change->from instanceof Undefined ? [] : [0 => $change->from]) +
                    ($change->to instanceof Undefined ? [] : [1 => $change->to]);
            }
        }

        /** @psalm-suppress InvalidPropertyAssignmentValue */
        $subEvent->fields = $out; // @phpstan-ignore-line - update with config helper later

        if (empty($subEvent->fields) && empty($subEvent->requirements)) {
            throw SubEventRequiredFieldsException::new([
                $subEvent->label,
                $class,
            ]);
        }
    }
}
