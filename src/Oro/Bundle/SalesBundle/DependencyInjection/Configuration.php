<?php

namespace Oro\Bundle\SalesBundle\DependencyInjection;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('oro_sales');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->arrayNode('api')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('customer_association_names')
                            ->info(
                                'The names that should be used for customer associations in Account API.'
                                . ' Use this config when automatically generated names are not correct.'
                            )
                            ->example(['Acme\AppBundle\Entity\Customer' => 'acmeCustomers'])
                            ->useAttributeAsKey('name')
                            ->normalizeKeys(false)
                            ->prototype('scalar')->cannotBeEmpty()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        $defaults = [
            'opportunity_status.in_progress'               => 0,
            'opportunity_status.identification_alignment'  => 0.1,
            'opportunity_status.needs_analysis'            => 0.2,
            'opportunity_status.solution_development'      => 0.5,
            'opportunity_status.negotiation'               => 0.8,
            'opportunity_status.won'                       => 1,
            'opportunity_status.lost'                      => 0,
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
