<?php

namespace Oro\Bundle\ChannelBundle\Datagrid;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Defines configuration schema for {@see ChannelLimitationExtension}.
 */
class ChannelLimitationExtensionConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder('root');

        $builder->getRootNode()
            ->children()
                ->scalarNode('channel_relation_path')
                    ->defaultValue('.dataChannel')
                    ->validate()
                        ->ifTrue(
                            function ($value) {
                                return str_contains((string)$value, '.') && substr_count((string)$value, '.') !== 1;
                            }
                        )
                        ->thenInvalid('Must contains relative path with single nesting')
                    ->end()
                    ->info(
                        'Path to Channel entity in the select statement. ' .
                        'Root entity should be passed as "." without alias'
                    )
                ->end()
            ->end();

        return $builder;
    }
}
