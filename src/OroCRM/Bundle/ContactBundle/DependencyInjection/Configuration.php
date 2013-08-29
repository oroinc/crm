<?php

namespace OroCRM\Bundle\ContactBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use OroCRM\Bundle\ContactBundle\Model\Social;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder
            ->root('orocrm_contact')
            ->children()
                ->arrayNode('social_url_format')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode(Social::TWITTER)
                            ->cannotBeEmpty()
                            ->defaultValue('https://twitter.com/%%username%%')
                        ->end()
                        ->scalarNode(Social::FACEBOOK)
                            ->cannotBeEmpty()
                            ->defaultValue('https://www.facebook.com/%%username%%')
                        ->end()
                        ->scalarNode(Social::GOOGLE_PLUS)
                            ->cannotBeEmpty()
                            ->defaultValue('https://profiles.google.com/%%username%%')
                        ->end()
                        ->scalarNode(Social::LINKED_IN)
                            ->cannotBeEmpty()
                            ->defaultValue('http://www.linkedin.com/in/%%username%%')
                        ->end()
                    ->end()
                ->end()
            ->end();

        SettingsBuilder::append(
            $rootNode,
            array(
                'name_format' => array(
                    'value' => str_replace('%', '%%', '%prefix% %first% %last% %suffix%')
                ),
            )
        );

        return $treeBuilder;
    }
}
