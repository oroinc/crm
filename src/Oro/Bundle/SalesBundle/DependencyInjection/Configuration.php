<?php

namespace Oro\Bundle\SalesBundle\DependencyInjection;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your config files
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('oro_sales');
        $rootNode = $treeBuilder->getRootNode();

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
                'display_relevant_opportunities' => [
                    'value' => true,
                    'type'  => 'boolean',
                ],
            ]
        );

        return $treeBuilder;
    }
}
