<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Doctrine\EntityFixtures;

return static function (ContainerConfigurator $container) {
    $services = $container->services()
        ->defaults()
        ->public();

    $services->set(EntityFixtures::class)->tag('doctrine.fixture.orm');
};
