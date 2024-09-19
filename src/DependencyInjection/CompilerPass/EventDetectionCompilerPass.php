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
use DualMedia\DoctrineEventConverterBundle\DependencyInjection\Model\EventConfiguration;
use DualMedia\DoctrineEventConverterBundle\DependencyInjection\Model\SubEventConfiguration;
use DualMedia\DoctrineEventConverterBundle\DoctrineEventConverterBundle;
use DualMedia\DoctrineEventConverterBundle\Event\AbstractEntityEvent;
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
use DualMedia\DoctrineEventConverterBundle\Model\Change;
use DualMedia\DoctrineEventConverterBundle\Model\Undefined;
use DualMedia\DoctrineEventConverterBundle\Proxy\Generator;
use DualMedia\DoctrineEventConverterBundle\Service\EventService;
use DualMedia\DoctrineEventConverterBundle\Service\SubEventService;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;

class EventDetectionCompilerPass implements CompilerPassInterface
{
    /**
     * Array of maps of the doctrine events to something we can actually find quickly.
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
        ContainerBuilder $container,
    ): void {
        if (!$container->hasDefinition(Generator::class)
            || !$container->has(Generator::class)
            || !$container->hasDefinition(EventService::class)
            || !$container->hasDefinition(SubEventService::class)) {
            return;
        }

        /** @var Generator $generator */
        $generator = $container->get(Generator::class);
        $mainEventService = $container->getDefinition(EventService::class);
        $subEventService = $container->getDefinition(SubEventService::class);

        /** @var array<class-string<AbstractEntityEvent>, non-empty-list<EventConfiguration>> $events */
        $events = [];

        /** @var array<class-string<AbstractEntityEvent>, non-empty-list<SubEventConfiguration>> $subEvents */
        $subEvents = [];

        $finder = new Finder();

        /** @var string $path */
        $path = $container->getParameter(DoctrineEventConverterBundle::CONFIGURATION_ROOT.'.parent_directory');
        /** @var string $namespace */
        $namespace = $container->getParameter(DoctrineEventConverterBundle::CONFIGURATION_ROOT.'.parent_namespace');

        // attempt to expand paths
        $match = [];
        preg_match_all('/%(.+)%/', $path, $match);

        foreach ($match[0] as $i => $item) {
            /** @var string $param */
            $param = $container->getParameter($match[1][$i]);
            $path = str_replace($item, $param, $path);
        }

        /**
         * This just lets us see if some name will be taken at any point, since SubEvents are created from a single class, they could collide.
         *
         * @var string[] $uniqueSubEventNames
         */
        $uniqueSubEventNames = [];

        $nonGlobPath = rtrim($path, '*\\/');

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

            foreach ($attributes as $attribute) {
                if ($attribute instanceof Event) {
                    $this->validateEventReflection($class, $reflection);
                    $entities = $this->getEntityClasses($attribute, $reflection);
                    $this->validateEntityClasses($entities, $class);

                    /** @var non-empty-list<class-string<EntityInterface>> $entities */
                    if (!array_key_exists($class, $events)) {
                        $events[$class] = [];
                    }

                    $config = (new EventConfiguration())
                        ->setEntities($entities)
                        ->setType($attribute->getType())
                        ->setAfterFlush($attribute->afterFlush);

                    $events[$class][] = $config;
                } elseif ($attribute instanceof SubEvent) {
                    $this->validateEventReflection($class, $reflection);
                    $entities = $this->getEntityClasses($attribute, $reflection);
                    $this->validateEntityClasses($entities, $class);
                    /** @var non-empty-list<class-string<EntityInterface>> $entities */
                    $config = (new SubEventConfiguration())
                        ->setEntities($entities)
                        ->setEvents($this->getSubEventTypes($attribute, $class))
                        ->setChanges($this->getChanges($attribute->changes))
                        ->setLabel($attribute->label)
                        ->setRequirements($attribute->requirements)
                        ->setPriority($attribute->priority)
                        ->setAllMode($attribute->allMode)
                        ->setAfterFlush($attribute->afterFlush)
                        ->validate($class);

                    if (!array_key_exists($class, $subEvents)) {
                        $subEvents[$class] = [];
                    }

                    $subEvents[$class][] = $config;

                    $uniq = $class.ucfirst($attribute->label);

                    if (in_array($uniq, $uniqueSubEventNames)) {
                        throw SubEventNameCollisionException::new([
                            $class,
                            $attribute->label,
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

        /**
         * @var array<int, list<array<int, mixed>>> $subEventConstruct
         */
        $subEventConstruct = [];

        // we're starting with sub events because those might need an implicit creation of main events
        /** @var class-string<AbstractEntityEvent> $class */
        foreach ($subEvents as $class => $configurations) {
            foreach ($configurations as $configuration) {
                /** @psalm-suppress InvalidArgument */
                if (!array_key_exists($class, $events)) {
                    $events[$class] = [];
                }

                foreach ($configuration->getEvents() as $type) {
                    $found = false;

                    for ($i = 0; $i < count($events[$class]); $i++) {
                        if ($type === $events[$class][$i]->getType()) {
                            $found = true;
                            break;
                        }
                    }

                    if ($found) {
                        continue;
                    }

                    $events[$class][] = (new EventConfiguration())
                        ->setEntities($configuration->getEntities())
                        ->setType($type);
                }

                // create and add sub events
                $out = $generator->generateProxyClass(
                    $class,
                    $configuration->getLabel(),
                    [SubEventInterface::class]
                );

                if (!array_key_exists($configuration->getPriority(), $subEventConstruct)) {
                    $subEventConstruct[$configuration->getPriority()] = [];
                }

                $subEventConstruct[$configuration->getPriority()][] = [
                    $out,
                    $configuration->getEntities(),
                    $configuration->isAllMode(),
                    $configuration->getChanges(),
                    $configuration->getRequirements(),
                    $configuration->getEvents(),
                    $configuration->isAfterFlush(),
                ];
            }
        }

        /** @var list<array<int, mixed>> $output */
        $output = [];
        krsort($subEventConstruct, SORT_NUMERIC); // sort by priorities (200 -> 0 -> -200)

        foreach ($subEventConstruct as $prioritySortedList) {
            foreach ($prioritySortedList as $data) {
                $output[] = $data;
            }
        }

        $subEventService->setArgument('$entries', $output);

        /** @var list<array<int, string>> $construct */
        $construct = [];

        // create and add main events
        /** @var class-string<AbstractEntityEvent> $class */
        foreach ($events as $class => $configurations) {
            foreach ($configurations as $configuration) {
                $out = $generator->generateProxyClass(
                    $class,
                    $configuration->getType(),
                    [MainEventInterface::class]
                );
                $construct[] = [
                    $out,
                    $configuration->getEntities(),
                    $configuration->getType(),
                    $configuration->isAfterFlush(),
                ];
            }
        }

        /** @see EventService::__construct() */
        $mainEventService->setArgument('$entries', $construct);
    }

    /**
     * @throws AbstractEntityEventNotExtendedException
     * @throws TargetClassFinalException
     */
    private function validateEventReflection(
        string $class,
        \ReflectionClass $reflection,
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
     * @return list<class-string>
     *
     * @throws NoValidEntityFoundException
     */
    private function getEntityClasses(
        SubEvent|Event $annotation,
        \ReflectionClass $reflection,
    ): array {
        $entities = $annotation->entity;

        if (!is_array($entities)) {
            if (!mb_strlen($class = call_user_func($reflection->getName().'::getEntityClass') ?? '')) { // @phpstan-ignore-line
                throw NoValidEntityFoundException::new([$reflection->getName()]);
            }

            /** @var class-string $class */
            $entities = [$class];
        }

        return $entities;
    }

    /**
     * @param list<class-string> $classes
     *
     * @throws EntityInterfaceMissingException
     */
    private function validateEntityClasses(
        array $classes,
        string $eventClass,
    ): void {
        foreach ($classes as $cls) {
            if (is_subclass_of($cls, EntityInterface::class)) { // fits by inheritance
                return;
            }

            throw EntityInterfaceMissingException::new([$cls, EntityInterface::class, $eventClass]);
        }
    }

    /**
     * @return non-empty-list<string>
     *
     * @throws UnknownEventTypeException
     */
    private function getSubEventTypes(
        SubEvent $event,
        string $class,
    ): array {
        $types = $event->types;

        if (empty($types)) {
            $types = [Events::postUpdate];
        }

        if (!empty($v = array_filter($types, fn (string $s) => !array_key_exists($s, self::DOCTRINE_TO_ANNOTATION_MAP)))) {
            throw UnknownEventTypeException::new([
                implode(', ', $v),
                $class,
            ]);
        }

        return $types;
    }

    /**
     * @param list<Change> $changes
     *
     * @return array<string, null|array{0?: mixed, 1?: mixed}>
     */
    private function getChanges(
        array $changes,
    ): array {
        /** @var array<string, null|array{0?: mixed, 1: mixed}> $out */
        $out = [];

        foreach ($changes as $change) {
            if ($change->from instanceof Undefined && $change->to instanceof Undefined) {
                $out[$change->name] = null;
            } else {
                // we must not modify the keys of the arrays
                $out[$change->name] =
                    ($change->from instanceof Undefined ? [] : [0 => $change->from]) +
                    ($change->to instanceof Undefined ? [] : [1 => $change->to]);
            }
        }

        return $out;
    }
}
