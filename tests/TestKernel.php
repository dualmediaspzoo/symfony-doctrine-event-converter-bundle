<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests;

use DAMA\DoctrineTestBundle\DAMADoctrineTestBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use DualMedia\DoctrineEventConverterBundle\DoctrineEventConverterBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel
{
    use MicroKernelTrait;

    /**
     * @return list<BundleInterface>
     */
    public function registerBundles(): array
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new DoctrineEventConverterBundle(),
            new DoctrineFixturesBundle(),
            new DAMADoctrineTestBundle(),
        ];
    }

    /**
     * @noinspection PhpUnusedPrivateMethodInspection
     * @phpstan-ignore-next-line
     */
    private function configureContainer(
        ContainerConfigurator $container,
        LoaderInterface $loader,
        ContainerBuilder $builder
    ): void {
        $loader->load(__DIR__.'/../config/services_test.php');

        $container->extension(DoctrineEventConverterBundle::CONFIGURATION_ROOT, [
            'parent_directory' => realpath(__DIR__ . '/Fixtures/Event'),
            'parent_namespace' => 'DualMedia\\DoctrineEventConverterBundle\\Tests\\Fixtures\\Event',
        ]);

        $container->extension('framework', [
            'test' => true,
            'secret' => 'OpenSecret',
        ]);

        $container->extension('doctrine', [
            'dbal' => [
                'driver' => 'pdo_sqlite',
                'path' => '%kernel.cache_dir%/test_db.sqlite',
            ],

            'orm' => [
                'auto_generate_proxy_classes' => true,
                'auto_mapping' => true,
                'mappings' => [
                    'DualMedia\\DoctrineEventConverterBundle\\Tests\\Fixtures\\' => [
                        'is_bundle' => false,
                        'type' => 'attribute',
                        'dir' => '%kernel.project_dir%/tests/Fixtures/Entity',
                        'prefix' => 'DualMedia\\DoctrineEventConverterBundle\\Tests\\Fixtures\\',
                        'alias' => 'app',
                    ],
                ],
            ],
        ]);
    }
}
