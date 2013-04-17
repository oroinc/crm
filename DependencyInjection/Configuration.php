<?php

namespace Oro\Bundle\FilterBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    const DEFAULT_LAYOUT = 'OroFilterBundle:Filter:layout.html.twig';

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('oro_filter');

        $rootNode
            ->children()
                ->arrayNode('twig')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('layout')
                            ->cannotBeEmpty()
                            ->defaultValue(self::DEFAULT_LAYOUT)
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
