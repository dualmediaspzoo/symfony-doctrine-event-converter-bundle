<?php

namespace DualMedia\DoctrineEventConverterBundle\DependencyInjection;

use DualMedia\DoctrineEventConverterBundle\DoctrineEventConverterBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension as SymfonyExtension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class DoctrineEventConverterExtension extends SymfonyExtension
{
    /**
     * @param array<string, mixed> $configs
     *
     * @throws \Exception
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function load(
        array $configs,
        ContainerBuilder $container
    ): void {
        $loader = new PhpFileLoader(
            $container,
            new FileLocator(__DIR__.'/../../config')
        );
        $loader->load('services.php');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter(DoctrineEventConverterBundle::CONFIGURATION_ROOT.'.parent_directory', $config['parent_directory']);
        $container->setParameter(DoctrineEventConverterBundle::CONFIGURATION_ROOT.'.parent_namespace', $config['parent_namespace']);
    }
}
