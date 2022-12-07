<?php

namespace DM\DoctrineEventDistributorBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension as SymfonyExtension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class EventDistributorExtension extends SymfonyExtension
{
    public function load(
        array $configs,
        ContainerBuilder $container
    ) {
        $loader = new PhpFileLoader(
            $container,
            new FileLocator(__DIR__.'/../../config')
        );
        $loader->load('services.php');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('event_distributor.parent_directory', $config['parent_directory']);
        $container->setParameter('event_distributor.parent_namespace', $config['parent_namespace']);
    }
}
