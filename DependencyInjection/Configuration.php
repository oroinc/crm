<?php

namespace Oro\Bundle\UserBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('oro_user');

        $rootNode->children()
            ->arrayNode('reset')
                ->addDefaultsIfNotSet()
                ->canBeUnset()
                ->children()
                    ->scalarNode('ttl')
                        ->defaultValue(86400)
                    ->end()
                ->end()
            ->end()
            ->arrayNode('email')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('address')
                        ->defaultValue('no-reply@example.com')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('name')
                        ->defaultValue('Oro Admin')
                        ->cannotBeEmpty()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
