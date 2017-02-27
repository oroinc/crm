<?php

namespace Oro\Bundle\SalesBundle\Datagrid\Extension\Customers;

use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;

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
        $queryBuilder       = $datasource->getQueryBuilder();
        $opportunityAlias   = $this->getEntityAlias($queryBuilder);
        $opportunityIdParam = ':opportunity_id';
        $queryBuilder->andWhere(sprintf('%s.id <> %s', $opportunityAlias, $opportunityIdParam));
        $queryBuilder->setParameter($opportunityIdParam, $opportunityId);
    }
}
