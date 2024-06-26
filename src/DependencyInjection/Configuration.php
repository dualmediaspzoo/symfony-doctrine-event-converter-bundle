<?php

namespace DualMedia\DoctrineEventConverterBundle\DependencyInjection;

use DualMedia\DoctrineEventConverterBundle\DoctrineEventConverterBundle;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $tree = new TreeBuilder(DoctrineEventConverterBundle::CONFIGURATION_ROOT);

        /**
         * @psalm-suppress UndefinedMethod
         *
         * @phpstan-ignore-next-line
         */
        $tree->getRootNode()
            ->children()
                ->scalarNode('parent_directory')->defaultValue('%kernel.project_dir%/src/*')->end()
                ->scalarNode('parent_namespace')->defaultValue('App')->end()
            ->end();

        return $tree;
    }
}
