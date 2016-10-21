<?php

namespace Oro\Bundle\SalesBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('oro_sales');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        $defaults = [
            'in_progress'               => 0,
            'identification_alignment'  => 0.1,
            'needs_analysis'            => 0.2,
            'solution_development'      => 0.5,
            'negotiation'               => 0.8,
            'won'                       => 1,
            'lost'                      => 0,
        ];

        SettingsBuilder::append(
            $rootNode,
            [
                'lead_feature_enabled' => [
                    'value' => true,
                    'type'  => 'boolean',
                ],
                'opportunity_feature_enabled' => [
                    'value' => true,
                    'type'  => 'boolean',
                ],
                'salesfunnel_feature_enabled' => [
                    'value' => false,
                    'type'  => 'boolean',
                ],
                'default_opportunity_probabilities' => [
                    'value' => $defaults,
                    'type' => 'array',
                ],
            ]
        );

        return $treeBuilder;
    }
}
