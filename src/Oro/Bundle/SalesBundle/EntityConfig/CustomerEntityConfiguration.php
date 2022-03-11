<?php

namespace Oro\Bundle\SalesBundle\EntityConfig;

use Oro\Bundle\EntityConfigBundle\EntityConfig\EntityConfigInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Provides validations entity config for customer scope.
 */
class CustomerEntityConfiguration implements EntityConfigInterface
{
    public function getSectionName(): string
    {
        return 'customer';
    }

    public function configure(NodeBuilder $nodeBuilder): void
    {
        $nodeBuilder
            ->node('enabled', 'normalized_boolean')
                ->info('`boolean` is used to enable the “customer” functionality.')
            ->end()
            ->scalarNode('associated_opportunity_block_priority')
                ->info('`integer` is the priority of associated opportunity grid block on the associated ' .
                    'customer entity.')
            ->end()
        ;
    }
}
