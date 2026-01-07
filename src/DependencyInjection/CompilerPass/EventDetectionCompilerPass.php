<?php

namespace DualMedia\DoctrineEventConverterBundle\DependencyInjection\CompilerPass;

use Doctrine\ORM\Events;
use DualMedia\DoctrineEventConverterBundle\Attribute\Event;
use DualMedia\DoctrineEventConverterBundle\Attribute\EventEntity;
use DualMedia\DoctrineEventConverterBundle\Attribute\PostPersistEvent;
use DualMedia\DoctrineEventConverterBundle\Attribute\PostRemoveEvent;
use DualMedia\DoctrineEventConverterBundle\Attribute\PostUpdateEvent;
use DualMedia\DoctrineEventConverterBundle\Attribute\PrePersistEvent;
use DualMedia\DoctrineEventConverterBundle\Attribute\PreRemoveEvent;
use DualMedia\DoctrineEventConverterBundle\Attribute\PreUpdateEvent;
use DualMedia\DoctrineEventConverterBundle\Attribute\SubEvent;
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
use DualMedia\DoctrineEventConverterBundle\Interface\EntityInterface;
use DualMedia\DoctrineEventConverterBundle\Interface\MainEventInterface;
use DualMedia\DoctrineEventConverterBundle\Interface\SubEventInterface;
use DualMedia\DoctrineEventConverterBundle\Model\Change;
use DualMedia\DoctrineEventConverterBundle\Model\Event as EventModel;
use DualMedia\DoctrineEventConverterBundle\Model\SubEvent as SubEventModel;
use DualMedia\DoctrineEventConverterBundle\Model\Undefined;
use DualMedia\DoctrineEventConverterBundle\Proxy\Generator;
use DualMedia\DoctrineEventConverterBundle\Storage\EventService;
use DualMedia\DoctrineEventConverterBundle\Storage\SubEventService;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Finder\Finder;

class EventDetectionCompilerPass implements CompilerPassInterface
{
    /**
     * Array of maps of the doctrine events to something we can actually find quickly.
     *
     * @var array<string, class-string<Event>>
     */
    public const array DOCTRINE_TO_ANNOTATION_MAP = [
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
    #[\Override]
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

        /** @var array<class-string<AbstractEntityEvent<EntityInterface>>, non-empty-list<EventConfiguration>> $events */
        $events = [];

        /** @var array<class-string<AbstractEntityEvent<EntityInterface>>, non-empty-list<SubEventConfiguration>> $subEvents */
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

            try {
                $reflection = new \ReflectionClass($class);
            } catch (\ReflectionException) {
                continue;
            }
            /** @var class-string $class */

            /** @var list<Event|SubEvent> $attributes */
            $attributes = [];

            foreach ($reflection->getAttributes() as $attribute) {
                $instance = $attribute->newInstance();

                if ($instance instanceof Event || $instance instanceof SubEvent) {
                    $attributes[] = $instance;
                }
            }

            if (empty($attributes)) {
                continue; // likely not our event class
            }

            $entities = $this->getEntityClasses($reflection);
            $this->validateEntityClasses($entities, $class);

            foreach ($attributes as $attribute) {
                if ($attribute instanceof Event) {
                    $this->validateEventReflection($class, $reflection);

                    /** @var non-empty-list<class-string<EntityInterface>> $entities */
                    if (!array_key_exists($class, $events)) {
                        $events[$class] = [];
                    }

                    $config = (new EventConfiguration())
                        ->setEntities($entities)
                        ->setType($attribute::EVENT_TYPE)
                        ->setAfterFlush($attribute->afterFlush);

                    $events[$class][] = $config;
                } elseif ($attribute instanceof SubEvent) {
                    $this->validateEventReflection($class, $reflection);
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

                    if (in_array($uniq, $uniqueSubEventNames, true)) {
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
         * @var array<int, list<array{proxyClass: class-string, configuration: SubEventConfiguration}>> $subEventConstruct
         */
        $subEventConstruct = [];

        // we're starting with sub events because those might need an implicit creation of main events
        /** @var class-string<AbstractEntityEvent<EntityInterface>> $class */
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
                    'proxyClass' => $out,
                    'configuration' => $configuration,
                ];
            }
        }

        /** @var list<array{proxyClass: class-string, configuration: SubEventConfiguration}> $output */
        $output = [];
        krsort($subEventConstruct, SORT_NUMERIC); // sort by priorities (200 -> 0 -> -200)

        foreach ($subEventConstruct as $prioritySortedList) {
            foreach ($prioritySortedList as $data) {
                $output[] = $data;
            }
        }

        $this->setSubEventServiceDefinition($container, $output);

        /** @var array<string, list<array{proxyClass: class-string, configuration: EventConfiguration}>> $eventMapped */
        $eventMapped = [];

        // create and add main events
        /** @var class-string<AbstractEntityEvent<EntityInterface>> $class */
        foreach ($events as $class => $configurations) {
            foreach ($configurations as $configuration) {
                $out = $generator->generateProxyClass(
                    $class,
                    $configuration->getType(),
                    [MainEventInterface::class]
                );

                if (!array_key_exists($doctrineEventType = $configuration->getType(), $eventMapped)) {
                    $eventMapped[$doctrineEventType] = [];
                }

                $eventMapped[$doctrineEventType][] = [
                    'proxyClass' => $out,
                    'configuration' => $configuration,
                ];
            }
        }

        $this->setEventServiceDefinition($container, $eventMapped);
    }

    /**
     * @param list<array{proxyClass: class-string, configuration: SubEventConfiguration}> $configuration
     */
    private function setSubEventServiceDefinition(
        ContainerBuilder $container,
        array $configuration
    ): void {
        /** @var array<class-string<EntityInterface>, list<SubEventModel>> $output */
        $output = [];

        foreach ($configuration as $data) {
            $config = $data['configuration'];
            $proxyClass = $data['proxyClass'];

            foreach ($config->getEntities() as $entityClass) {
                if (!array_key_exists($entityClass, $output)) {
                    $output[$entityClass] = [];
                }

                $output[$entityClass][] = new Definition(SubEventModel::class, [
                    $proxyClass,
                    $config->isAllMode(),
                    $config->getChanges(),
                    $config->getRequirements(),
                    $config->getEvents(),
                    $config->isAfterFlush(),
                ]);
            }
        }

        $container->getDefinition(SubEventService::class)->setArgument('$events', $output);
    }

    /**
     * @param array<string, list<array{proxyClass: class-string, configuration: EventConfiguration}>> $configuration
     */
    private function setEventServiceDefinition(
        ContainerBuilder $container,
        array $configuration
    ): void {
        /** @var array<string, array<class-string<EntityInterface>, list<string>>> $output */
        $output = [];

        foreach ($configuration as $doctrineEventType => $data) {
            if (!array_key_exists($doctrineEventType, self::DOCTRINE_TO_ANNOTATION_MAP)) {
                continue;
            }

            if (!array_key_exists($doctrineEventType, $output)) {
                $output[$doctrineEventType] = [];
            }

            foreach ($data as $item) {
                $config = $item['configuration'];

                foreach ($config->getEntities() as $entityClass) {
                    $output[$doctrineEventType][$entityClass] = new Definition(EventModel::class, [
                        $item['proxyClass'],
                        $config->isAfterFlush(),
                    ]);
                }
            }
        }

        $container->getDefinition(EventService::class)->setArgument('$events', $output);
    }

    /**
     * @param \ReflectionClass<object> $reflection
     *
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
     * @param \ReflectionClass<object> $reflection
     *
     * @return list<class-string>
     *
     * @throws NoValidEntityFoundException
     */
    private function getEntityClasses(
        \ReflectionClass $reflection
    ): array {
        $classes = [];

        foreach ($reflection->getAttributes(EventEntity::class) as $attribute) {
            /** @var EventEntity $real */
            $real = $attribute->newInstance();
            $classes[] = $real->class;
        }

        $classes = array_values(array_filter(array_unique($classes))); // @phpstan-ignore-line

        if (empty($classes)) {
            throw NoValidEntityFoundException::new([$reflection->getName()]);
        }

        return $classes;
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
