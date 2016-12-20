<?php

namespace Oro\Bundle\ChannelBundle\DependencyInjection;

use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ChannelConfiguration implements ConfigurationInterface
{
    const ROOT_NODE_NAME            = 'channels';
    const DEFAULT_CUSTOMER_IDENTITY = 'Oro\Bundle\ChannelBundle\Entity\CustomerIdentity';
    const DEFAULT_PRIORITY          = 0;

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $root        = $treeBuilder->root(self::ROOT_NODE_NAME);
        $root
            ->children()
                ->arrayNode(SettingsProvider::DATA_PATH)->isRequired()->cannotBeEmpty()
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
                            ->arrayNode('belongs_to')->cannotBeEmpty()
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
