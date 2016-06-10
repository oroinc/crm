<?php

namespace OroCRM\Bundle\SalesBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('oro_crm_sales');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        $defaults = [
            'lost' => 0,
            'in_progress' => .1,
            'needs_analysis' => .2,
            'solution_development' => .5,
            'negotiation' => .8,
            'won' => 1,
        ];

        SettingsBuilder::append(
            $rootNode,
            [
                'default_opportunity_probabilities' => [
                    'value' => $defaults,
                    'type' => 'array',
                ],
            ]
        );

        return $treeBuilder;
    }
}
