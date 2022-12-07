<?php

namespace DM\DoctrineEventDistributorBundle\DependencyInjection\CompilerPass;

use DM\DoctrineEventDistributorBundle\Annotation\Event;
use DM\DoctrineEventDistributorBundle\Annotation\PostPersistEvent;
use DM\DoctrineEventDistributorBundle\Annotation\PostRemoveEvent;
use DM\DoctrineEventDistributorBundle\Annotation\PostUpdateEvent;
use DM\DoctrineEventDistributorBundle\Annotation\PrePersistEvent;
use DM\DoctrineEventDistributorBundle\Annotation\PreRemoveEvent;
use DM\DoctrineEventDistributorBundle\Annotation\PreUpdateEvent;
use DM\DoctrineEventDistributorBundle\Annotation\SubEvent;
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
use DM\DoctrineEventDistributorBundle\Exception\Proxy\DirectoryNotWritable;
use DM\DoctrineEventDistributorBundle\Exception\Proxy\TargetClassFinalException as ProxyTargetClassFinalException;
use DM\DoctrineEventDistributorBundle\Exception\Proxy\TargetClassNamingSchemeInvalidException;
use DM\DoctrineEventDistributorBundle\Interfaces\EntityInterface;
use DM\DoctrineEventDistributorBundle\Interfaces\MainEventInterface;
use DM\DoctrineEventDistributorBundle\Interfaces\SubEventInterface;
use DM\DoctrineEventDistributorBundle\Proxy\Generator;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\Events;
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
     * @throws SubEventLabelMissingException
     * @throws UnknownEventTypeException
     * @throws ProxyTargetClassFinalException
     * @throws TargetClassNamingSchemeInvalidException
     * @throws SubEventNameCollisionException
     * @throws SubEventRequiredFieldsException
     * @throws \ReflectionException
     */
    public function process(
        ContainerBuilder $container
    ) {
        if (!$container->hasDefinition(Generator::class) ||
            !$container->has(Generator::class) ||
            !$container->hasDefinition(DispatchingSubscriber::class) ||
            !$container->has(Reader::class)) {
            return;
        }

        $reader = $container->get(Reader::class);
        /** @var Generator $generator */
        $generator = $container->get(Generator::class);
        $subscriber = $container->getDefinition(DispatchingSubscriber::class);

        /** @var array<class-string, non-empty-list<Event>> $events */
        $events = [];

        /** @var array<class-string, non-empty-list<SubEvent>> $subEvents */
        $subEvents = [];

        $finder = new Finder();

        /** @var string $path */
        $path = $container->getParameter('event_distributor.parent_directory');
        $namespace = $container->getParameter('event_distributor.parent_namespace');

        // attempt to expand paths
        $match = [];
        preg_match_all('/%(.+)%/', $path, $match);
        if (count($match[0] ?? [])) {
            foreach ($match[0] as $i => $item) {
                $param = $container->getParameter($match[1][$i]);
                $path = str_replace($item, $param, $path);
            }
        }

        /**
         * This just lets us see if some name will be taken at any point, since SubEvents are created from a single class, they could collide
         *
         * @var string[]
         */
        $uniqueSubEventNames = [];

        $nonGlobPath = rtrim($path, "*\\/");
        foreach ($finder->files()->in($path)->name('*.php') as $file) {
            $class = $namespace.'\\'.str_replace(['.php', '/'], ['', '\\'], mb_substr($file->getRealPath(), mb_strpos($file->getRealPath(), $nonGlobPath) + mb_strlen($nonGlobPath) + 1));
            try {
                if (false === ($annotations = $reader->getClassAnnotations($reflection = new \ReflectionClass($class)))) {
                    continue;
                }
            } catch (\ReflectionException $e) {
                continue;
            }

            foreach ($annotations as $annotation) {
                if ($annotation instanceof Event) {
                    $this->validateEventReflection($class, $reflection);
                    $this->applyEntityClass($annotation, $reflection);
                    $this->validateEntityClass($annotation->entity, $class);

                    if (!array_key_exists($class, $events)) {
                        $events[$class] = [];
                    }

                    $events[$class][] = $annotation;
                } elseif ($annotation instanceof SubEvent) {
                    $this->validateEventReflection($class, $reflection);
                    $this->applyEntityClass($annotation, $reflection);
                    $this->validateEntityClass($annotation->entity, $class);

                    if (null === $annotation->label) {
                        throw SubEventLabelMissingException::new([
                            $class,
                            implode(', ', $annotation->entity),
                        ]);
                    }

                    $this->updateSubEventAnnotationFields($annotation, $class);

                    if (!array_key_exists($class, $subEvents)) {
                        $subEvents[$class] = [];
                    }

                    $subEvents[$class][] = $annotation;

                    $uniq = $class.ucfirst($annotation->label);
                    if (false !== array_search($uniq, $uniqueSubEventNames)) {
                        throw SubEventNameCollisionException::new([
                            $class,
                            $annotation->label,
                        ]);
                    }

                    $uniqueSubEventNames[] = $uniq;
                }
            }
        }

        $cacheDir = $container->getParameter('kernel.cache_dir').DIRECTORY_SEPARATOR.EventDistributorBundle::CACHE_DIRECTORY;
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
        foreach ($subEvents as $class => $annotations) {
            foreach ($annotations as $annotation) {
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
                    $missing->entity = $annotation->entity;
                    $events[$class][] = $missing;
                }

                // create and add sub events
                $out = $generator->generateProxyClass(
                    $class,
                    $annotation->label,
                    [SubEventInterface::class]
                );
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
        foreach ($events as $class => $annotations) {
            foreach ($annotations as $annotation) {
                $out = $generator->generateProxyClass(
                    $class,
                    $annotation->getType(),
                    [MainEventInterface::class]
                );
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
        $annotation,
        \ReflectionClass $reflection
    ): void {
        if (!is_array($annotation->entity) && !mb_strlen($annotation->entity ?? '')) {
            if (!mb_strlen($annotation->entity = call_user_func($reflection->getName().'::getEntityClass') ?? '')) {
                throw NoValidEntityFoundException::new([$reflection->getName()]);
            }
        }

        if (!is_array($annotation->entity)) {
            /** @psalm-suppress InvalidPropertyAssignmentValue */
            $annotation->entity = [$annotation->entity];
        }
    }

    /**
     * @param string|string[] $class
     *
     * @throws EntityInterfaceMissingException
     */
    private function validateEntityClass(
        $class,
        string $eventClass
    ): void {
        if (!is_array($class)) {
            $class = [$class];
        }

        foreach ($class as $cls) {
            /** @psalm-suppress TypeDoesNotContainType */
            if (is_a($cls, EntityInterface::class) || is_subclass_of($cls, EntityInterface::class)) { // fits by inheritance
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

        $subEvent->fields = is_array($subEvent->fields) ? $subEvent->fields : [$subEvent->fields];
        $out = [];

        foreach ($subEvent->fields as $possibleName => $possibleValues) {
            if (is_numeric($possibleName) && is_string($possibleValues)) {
                $out[$possibleValues] = null;
            } elseif (is_array($possibleValues)) {
                $out[$possibleName] = $possibleValues;
            }
        }

        $subEvent->fields = $out;

        if (empty($subEvent->fields) && empty($subEvent->requirements)) {
            throw SubEventRequiredFieldsException::new([
                $subEvent->label,
                $class,
            ]);
        }
    }
}
