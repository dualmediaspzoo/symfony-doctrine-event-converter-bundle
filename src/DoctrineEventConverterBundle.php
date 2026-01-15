<?php

namespace DualMedia\DoctrineEventConverterBundle;

use DualMedia\DoctrineEventConverterBundle\DependencyInjection\CompilerPass\EventDetectionCompilerPass;
use DualMedia\DoctrineEventConverterBundle\Proxy\Generator;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * @phpstan-type DoctrineChangeArray array<string, array{0: mixed, 1: mixed}>
 */
class DoctrineEventConverterBundle extends AbstractBundle
{
    public const string CACHE_DIRECTORY = 'dm-smd-event-distributor-bundle';

    public const string CONFIGURATION_ROOT = 'doctrine_event_converter';

    public const string TAG_VERIFIER = 'dm.devb.verifier';

    /**
     * @var callable|null
     */
    private $autoloader;

    #[\Override]
    public function build(
        ContainerBuilder $container,
    ): void {
        $container->addCompilerPass(new EventDetectionCompilerPass());
    }

    #[\Override]
    public function configure(
        DefinitionConfigurator $definition
    ): void {
        $definition->rootNode() // @phpstan-ignore-line
            ->children()
                ->scalarNode('parent_directory')->defaultValue('%kernel.project_dir%/src/*')->end()
                ->scalarNode('parent_namespace')->defaultValue('App')->end()
            ->end();
    }

    /**
     * @param array<array-key, mixed> $config
     */
    #[\Override]
    public function loadExtension(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder
    ): void {
        $loader = new PhpFileLoader(
            $builder,
            new FileLocator(__DIR__.'/../config')
        );
        $loader->load('services.php');

        $builder->setParameter(DoctrineEventConverterBundle::CONFIGURATION_ROOT.'.parent_directory', $config['parent_directory']); // @phpstan-ignore-line
        $builder->setParameter(DoctrineEventConverterBundle::CONFIGURATION_ROOT.'.parent_namespace', $config['parent_namespace']); // @phpstan-ignore-line
    }

    #[\Override]
    public function boot(): void
    {
        $this->autoloader = function ($class) {
            if (0 !== mb_strpos($class, Generator::PROXY_NS)) {
                return;
            }

            /** @var Generator $generator */
            $generator = $this->container?->get(Generator::class);
            $file = $generator->resolveFilePath($class);

            if (file_exists($file)) {
                require $file;
            }
        };

        spl_autoload_register($this->autoloader);
    }

    #[\Override]
    public function shutdown(): void
    {
        if (null !== $this->autoloader) {
            spl_autoload_unregister($this->autoloader);
            $this->autoloader = null;
        }
    }
}
