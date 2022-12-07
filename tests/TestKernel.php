<?php

namespace DM\DoctrineEventDistributorBundle\Tests;

use DAMA\DoctrineTestBundle\DAMADoctrineTestBundle;
use DM\DoctrineEventDistributorBundle\EventDistributorBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): array
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new EventDistributorBundle(),
            new DoctrineFixturesBundle(),
            new DAMADoctrineTestBundle(),
        ];
    }

    /**
     * 4.4 compatibility
     *
     * @param RoutingConfigurator $routes
     */
    protected function configureRoutes(
        RoutingConfigurator $routes
    ): void {
    }

    /**
     * 4.4 compatibility
     *
     * @param ContainerBuilder $container
     * @param LoaderInterface $loader
     */
    public function configureContainer(
        ContainerBuilder $container,
        LoaderInterface $loader
    ): void {
        $loader->load(__DIR__.'/../config/services_test.php');

        $container->loadFromExtension('event_distributor', [
            'parent_directory' => realpath(__DIR__ . '/Fixtures/Event'),
            'parent_namespace' => 'DM\\EventDistributorBundle\\Tests\\Fixtures\\Event',
        ]);

        $container->loadFromExtension('framework', [
            'test' => true,
        ]);

        $container->loadFromExtension('doctrine', [
            'dbal' => [
                'driver' => 'pdo_sqlite',
                'path' => '%kernel.cache_dir%/test_db.sqlite',
            ],

            'orm' => [
                'auto_generate_proxy_classes' => true,
                'auto_mapping' => true,
                'mappings' => [
                    'DM\\EventDistributorBundle\\Tests\\Fixtures\\' => [
                        'is_bundle' => false,
                        'type' => 'annotation',
                        'dir' => '%kernel.project_dir%/tests/Fixtures/Entity',
                        'prefix' => 'DM\\EventDistributorBundle\\Tests\\Fixtures\\',
                        'alias' => 'app',
                    ],
                ],
            ],
        ]);
    }
}
