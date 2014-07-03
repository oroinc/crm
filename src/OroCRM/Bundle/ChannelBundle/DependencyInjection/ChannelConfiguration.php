<?php

namespace OroCRM\Bundle\ChannelBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ChannelConfiguration implements ConfigurationInterface
{
    const ROOT_NODE_NAME = 'orocrm_channel';

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $root        = $treeBuilder->root(self::ROOT_NODE_NAME);
        $root
            ->children()
                ->arrayNode('entity_data')->isRequired()->cannotBeEmpty()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')
                                ->isRequired()->cannotBeEmpty()
                            ->end()
                            ->arrayNode('dependent')
                                ->prototype('scalar')->cannotBeEmpty()->end()
                            ->end()
                            ->arrayNode('navigation_items')
                                ->prototype('scalar')->cannotBeEmpty()->end()
                            ->end()
                            ->arrayNode('dependencies')
                                ->prototype('scalar')->cannotBeEmpty()->end()
                            ->end()
                            ->scalarNode('dependenciesCondition')
                                ->defaultValue('AND')->cannotBeEmpty()
                                ->validate()->ifNotInArray(['OR', 'AND'])
                                    ->thenInvalid('Invalid param %s')
                                ->end()
                            ->end()
                            ->scalarNode('belongs_to_integration')->cannotBeEmpty()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
