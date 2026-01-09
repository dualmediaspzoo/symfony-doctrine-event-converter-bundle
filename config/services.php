<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\ORM\Events;
use DualMedia\DoctrineEventConverterBundle\DelayableEventDispatcher;
use DualMedia\DoctrineEventConverterBundle\DoctrineEventConverterBundle as Bundle;
use DualMedia\DoctrineEventConverterBundle\EventSubscriber\DispatchingSubscriber;
use DualMedia\DoctrineEventConverterBundle\Proxy\Generator;
use DualMedia\DoctrineEventConverterBundle\Storage\EventService;
use DualMedia\DoctrineEventConverterBundle\Storage\SubEventService;
use DualMedia\DoctrineEventConverterBundle\Verifier\FieldVerifier;
use DualMedia\DoctrineEventConverterBundle\Verifier\RequirementVerifier;
use DualMedia\DoctrineEventConverterBundle\Verifier\SubEventVerifier;
use DualMedia\DoctrineEventConverterBundle\Verifier\TypeVerifier;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\Reference;

return static function (ContainerConfigurator $container) {
    $services = $container->services()
        ->defaults()
        ->private();

    $services->set(DelayableEventDispatcher::class)
        ->arg(0, new Reference('event_dispatcher'))
        ->public();

    $services->set(EventService::class)
        ->lazy();

    $services->set(SubEventService::class)
        ->lazy();

    $services->set(FieldVerifier::class)
        ->tag(Bundle::TAG_VERIFIER);

    $services->set(RequirementVerifier::class)
        ->tag(Bundle::TAG_VERIFIER, ['priority' => 70]);

    $services->set(TypeVerifier::class)
        ->tag(Bundle::TAG_VERIFIER, ['priority' => 100]);

    $services->set(SubEventVerifier::class)
        ->arg('$verifiers', new TaggedIteratorArgument(Bundle::TAG_VERIFIER));

    $services->set(DispatchingSubscriber::class)
        ->arg('$eventService', new Reference(EventService::class))
        ->arg('$subEventService', new Reference(SubEventService::class))
        ->arg('$dispatcher', new Reference(DelayableEventDispatcher::class))
        ->arg('$subEventVerifier', new Reference(SubEventVerifier::class))
        ->tag('doctrine.event_listener', [
            'event' => Events::prePersist,
        ])
        ->tag('doctrine.event_listener', [
            'event' => Events::postPersist,
        ])
        ->tag('doctrine.event_listener', [
            'event' => Events::preUpdate,
        ])
        ->tag('doctrine.event_listener', [
            'event' => Events::postUpdate,
        ])
        ->tag('doctrine.event_listener', [
            'event' => Events::preRemove,
        ])
        ->tag('doctrine.event_listener', [
            'event' => Events::postRemove,
        ])
        ->tag('doctrine.event_listener', [
            'event' => Events::preFlush,
        ])
        ->tag('doctrine.event_listener', [
            'event' => Events::postFlush,
        ]);

    $services->set(Generator::class)
        ->arg(0, '%kernel.cache_dir%/dm-smd-event-distributor-bundle')
        ->public(); // required for autoloader
};
