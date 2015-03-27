<?php

namespace OroCRM\Bundle\MagentoBundle\Service\AutomaticDiscovery;

use Doctrine\ORM\QueryBuilder;

interface DiscoveryStrategyInterface
{
    /**
     * @param QueryBuilder $qb
     * @param string $rootAlias
     * @param string $field
     * @param mixed $configuration
     * @param object $entity
     * @return
     */
    public function apply(QueryBuilder $qb, $rootAlias, $field, $configuration, $entity);
}
