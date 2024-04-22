<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\ORM\Events;
use DualMedia\DoctrineEventConverterBundle\EventSubscriber\DispatchingSubscriber;
use DualMedia\DoctrineEventConverterBundle\Proxy\Generator;
use DualMedia\DoctrineEventConverterBundle\Service\DelayableEventDispatcher;
use DualMedia\DoctrineEventConverterBundle\Service\EventService;
use DualMedia\DoctrineEventConverterBundle\Service\SubEventService;
use DualMedia\DoctrineEventConverterBundle\Service\VerifierService;
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

    $services->set(VerifierService::class);

    $def = $services->set(DispatchingSubscriber::class)
        ->arg('$eventService', new Reference(EventService::class))
        ->arg('$subEventService', new Reference(SubEventService::class))
        ->arg('$verifierService', new Reference(VerifierService::class))
        ->arg('$dispatcher', new Reference(DelayableEventDispatcher::class))
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
