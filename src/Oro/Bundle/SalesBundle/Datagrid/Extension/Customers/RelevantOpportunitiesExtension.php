<?php

namespace Oro\Bundle\SalesBundle\Datagrid\Extension\Customers;

use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;

class RelevantOpportunitiesExtension extends AccountOpportunitiesExtension
{
    /**
     * {@inheritdoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        return $this->parameters->get('opportunity_id') && parent::isApplicable($config);
    }

    /**
     * @param  DatagridConfiguration $config
     * @param  DatasourceInterface   $datasource
     */
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
        $opportunityId = $this->parameters->get('opportunity_id');
        $queryBuilder  = $datasource->getQueryBuilder();
        $opportunityAlias = $this->getOpportunityAlias($queryBuilder);
        $opportunityIdParam = ':opportunity_id';
        $queryBuilder->andWhere(sprintf('%s.id <> %s', $opportunityAlias, $opportunityIdParam));
        $queryBuilder->setParameter($opportunityIdParam, $opportunityId);
    }
}
