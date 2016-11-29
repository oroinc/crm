<?php

namespace Oro\Bundle\SalesBundle\Datagrid\Extension\Customers;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Exception\DatasourceException;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;

use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Provider\Customer\AccountCustomerHelper;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider;

class OpportunitiesExtension extends AbstractExtension
{
    /** @var ConfigProvider */
    protected $customerConfigProvider;

    /**
     * @param ConfigProvider $customerConfigProvider
     */
    public function __construct(ConfigProvider $customerConfigProvider)
    {
        $this->customerConfigProvider = $customerConfigProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        return
            $config->getDatasourceType() === OrmDatasource::TYPE &&
            $this->parameters->get('customer_class') &&
            $this->parameters->get('customer_id') &&
            $this->customerConfigProvider->isCustomerClass($this->parameters->get('customer_class'));
    }

    /**
     * {@inheritdoc}
     */
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
        /** @var $datasource OrmDataSource */
        $customerClass    = $this->parameters->get('customer_class');
        $customerField    = $this->getCustomerField($customerClass);
        $queryBuilder     = $datasource->getQueryBuilder();
        $customerIdParam  = sprintf(':customerIdParam_%s', $customerField);
        $opportunityAlias = $this->getOpportunityAlias($queryBuilder);
        $customerAlias    = 'customer';
        $queryBuilder->join(sprintf('%s.customerAssociation', $opportunityAlias), $customerAlias);
        $queryBuilder->andWhere(
            sprintf(
                '%s.%s = %s',
                $customerAlias,
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
     *
     * @throws DatasourceException
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

    /**
     * @param $customerClass
     *
     * @return string
     */
    protected function getCustomerField($customerClass)
    {
        return AccountCustomerHelper::getCustomerTargetField($customerClass);
    }
}
