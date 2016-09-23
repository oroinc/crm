<?php

namespace Oro\Bundle\MagentoBundle\Service\AutomaticDiscovery;

use Doctrine\ORM\QueryBuilder;

interface DiscoveryStrategyInterface
{
    /**
     * @param QueryBuilder $qb
     * @param string $rootAlias
     * @param string $field
     * @param array $configuration
     * @param object $entity
     * @return
     */
    public function apply(QueryBuilder $qb, $rootAlias, $field, array $configuration, $entity);
}
