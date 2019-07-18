<?php

namespace Oro\Bundle\SalesBundle\Datagrid\Extension\Customers;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Component\DoctrineUtils\ORM\QueryBuilderUtil;

/**
 * Restrict related entities grid by account and relevant opportunity.
 */
class RelevantOpportunitiesExtension extends AccountRelatedEntitiesExtension
{
    /**
     * {@inheritdoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        return
            $this->parameters->get('opportunity_id')
            && parent::isApplicable($config);
    }

    /**
     * {@inheritdoc}
     */
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
        /** @var OrmDatasource $datasource */
        $opportunityId      = $this->parameters->get('opportunity_id');
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder       = $datasource->getQueryBuilder();
        $opportunityAlias   = $this->getEntityAlias($queryBuilder);
        $opportunityIdParam = ':opportunity_id';
        $queryBuilder->andWhere(
            $queryBuilder->expr()->neq(QueryBuilderUtil::getField($opportunityAlias, 'id'), $opportunityIdParam)
        );
        $queryBuilder->setParameter($opportunityIdParam, $opportunityId);
    }
}
