<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use DualMedia\DoctrineEventConverterBundle\EventSubscriber\DispatchingSubscriber;
use DualMedia\DoctrineEventConverterBundle\Proxy\Generator;
use Symfony\Component\DependencyInjection\Reference;

return static function (ContainerConfigurator $container) {
    $services = $container->services()
        ->defaults()
        ->private();

    $services->set(DispatchingSubscriber::class)
        ->arg(0, new Reference('event_dispatcher'))
        ->tag('doctrine.event_subscriber');

    $services->set(Generator::class)
        ->arg(0, '%kernel.cache_dir%/dm-smd-event-distributor-bundle')
        ->public(); // required for autoloader
};
