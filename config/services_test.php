<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use DualMedia\DoctrineEventConverterBundle\Storage\EventService;
use DualMedia\DoctrineEventConverterBundle\Storage\SubEventService;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Doctrine\EntityFixtures;

return static function (ContainerConfigurator $container) {
    $services = $container->services()
        ->defaults()
        ->public();

    $services->set(EventService::class);
    $services->set(SubEventService::class);

    $services->set(EntityFixtures::class)->tag('doctrine.fixture.orm');
};
