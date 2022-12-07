<?php

namespace DM\DoctrineEventDistributorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $tree = new TreeBuilder('event_distributor');

        // @phpstan-ignore-next-line
        $tree->getRootNode()
            ->children()
                ->scalarNode('parent_directory')->defaultValue('%kernel.project_dir%/src/*')->end()
                ->scalarNode('parent_namespace')->defaultValue('App')->end()
            ->end();


        return $tree;
    }
}
