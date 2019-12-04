<?php

namespace Oro\Bundle\ChannelBundle\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Provides schema for configuration that is loaded from "Resources/config/oro/channels.yml" files.
 */
class ChannelConfiguration implements ConfigurationInterface
{
    public const ROOT_NODE = 'channels';

    private const DEFAULT_CUSTOMER_IDENTITY = 'Oro\Bundle\ChannelBundle\Entity\CustomerIdentity';
    private const DEFAULT_PRIORITY          = 0;

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder(self::ROOT_NODE);
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->arrayNode('entity_data')->isRequired()->requiresAtLeastOneElement()
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
                            ->scalarNode('dependencies_condition')
                                ->defaultValue('AND')->cannotBeEmpty()
                                ->validate()->ifNotInArray(['OR', 'AND'])
                                    ->thenInvalid('Invalid param %s')
                                ->end()
                            ->end()
                            ->arrayNode('belongs_to')
                                ->children()
                                    ->scalarNode('integration')->cannotBeEmpty()->end()
                                    ->scalarNode('connector')->cannotBeEmpty()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('channel_types')->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('label')->isRequired()->cannotBeEmpty()->end()
                            ->arrayNode('entities')
                                ->treatNullLike([])
                                ->prototype('scalar')
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()
                            ->scalarNode('integration_type')->cannotBeEmpty()->end()
                            ->scalarNode('customer_identity')
                                ->cannotBeEmpty()
                                ->defaultValue(self::DEFAULT_CUSTOMER_IDENTITY)
                            ->end()
                            ->scalarNode('lifetime_value')->cannotBeEmpty()->end()
                            ->booleanNode('system')->defaultFalse()->end()
                            ->integerNode('priority')->defaultValue(self::DEFAULT_PRIORITY)->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
