<?php

namespace Oro\Bundle\SalesBundle\Datagrid\Extension\Customers;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Exception\DatasourceException;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\EntityConfig\CustomerScope;
use Oro\Bundle\SalesBundle\Provider\CustomerConfigProvider;

class OpportunitiesExtension extends AbstractExtension
{
    /** @var CustomerConfigProvider */
    protected $customerConfigProvider;

    /**
     * @param CustomerConfigProvider $customerConfigProvider
     */
    public function __construct(CustomerConfigProvider $customerConfigProvider)
    {
        $this->customerConfigProvider = $customerConfigProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        return
            false &&// todo
            $config->getDatasourceType() === OrmDatasource::TYPE &&
            $this->parameters->get('customer_class') &&
            $this->parameters->get('customer_id') &&
            $this->customerConfigProvider->hasAssociatedCustomerClass($this->parameters->get('customer_class'));
    }

    /**
     * {@inheritdoc}
     */
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
        /** @var $datasource OrmDataSource */
        $customerClass = $this->parameters->get('customer_class');
        $customerField    = ExtendHelper::buildAssociationName(
            $customerClass,
            CustomerScope::ASSOCIATION_KIND
        );
        $queryBuilder     = $datasource->getQueryBuilder();
        $customerIdParam  = sprintf(':customerIdParam_%s', $customerField);
        $opportunityAlias = $this->getOpportunityAlias($queryBuilder);
        $queryBuilder->andWhere(
            sprintf(
                '%s.%s = %s',
                $opportunityAlias,
                $customerField,
                $customerIdParam
            )
        );
        $queryBuilder->setParameter($customerIdParam, $this->parameters->get('customer_id'));
    }

    /**
     * @param QueryBuilder $qb
     *
     * @return string
     */
    protected function getOpportunityAlias(QueryBuilder $qb)
    {
        $fromParts = $qb->getDQLPart('from');
        /** @var $fromPart Expr\From */
        foreach ($fromParts as $fromPart) {
            if ($fromPart->getFrom() === Opportunity::class) {
                return $fromPart->getAlias();
            }
        }

        throw new DatasourceException('Couldn\'t find Opportunities alias in QueryBuilder.');
    }
}
