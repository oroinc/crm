<?php

namespace Oro\Bundle\MagentoBundle\Service\AutomaticDiscovery;

use Doctrine\ORM\QueryBuilder;

/**
 * An interface to define a discovery strategy
 */
interface DiscoveryStrategyInterface
{
    /**
     * @param QueryBuilder $qb
     * @param string $rootAlias
     * @param string $field
     * @param array $configuration
     * @param object $entity
     */
    public function apply(QueryBuilder $qb, $rootAlias, $field, array $configuration, $entity);
}
