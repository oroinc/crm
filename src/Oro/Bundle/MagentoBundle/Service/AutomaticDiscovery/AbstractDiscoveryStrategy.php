<?php

namespace Oro\Bundle\MagentoBundle\Service\AutomaticDiscovery;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\MagentoBundle\DependencyInjection\Configuration;
use Oro\Component\DoctrineUtils\ORM\QueryBuilderUtil;

abstract class AbstractDiscoveryStrategy implements DiscoveryStrategyInterface
{
    /**
     * @param QueryBuilder $qb
     * @param string $qbFieldName
     * @param string $qbParameterName
     * @param array $configuration
     * @return \Doctrine\ORM\Query\Expr\Base
     */
    protected function getFieldExpr(QueryBuilder $qb, $qbFieldName, $qbParameterName, array $configuration)
    {
        QueryBuilderUtil::checkParameter($qbParameterName);
        $fieldExpr = $qb->expr()->eq($qbFieldName, $qbParameterName);

        $options = $configuration[Configuration::DISCOVERY_OPTIONS_KEY];
        if (!empty($options[Configuration::DISCOVERY_EMPTY_KEY])) {
            $fieldExpr = $qb->expr()->orX(
                $fieldExpr,
                $qb->expr()->eq($qbFieldName, ':emptyValue'),
                $qb->expr()->isNull($qbFieldName)
            );
            $qb->setParameter('emptyValue', '');
        }

        return $fieldExpr;
    }
}
