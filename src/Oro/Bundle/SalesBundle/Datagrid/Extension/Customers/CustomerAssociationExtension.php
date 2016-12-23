<?php

namespace Oro\Bundle\SalesBundle\Datagrid\Extension\Customers;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Exception\DatasourceException;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\DataGridBundle\Tools\GridConfigurationHelper;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider;

class CustomerAssociationExtension extends AbstractExtension
{
    /** @var ConfigProvider */
    protected $customerConfigProvider;

    /** @var GridConfigurationHelper */
    protected $gridConfigurationHelper;

    /** @var string */
    protected $entityClassName;

    /**
     * @param ConfigProvider $customerConfigProvider
     * @param GridConfigurationHelper $gridConfigurationHelper
     */
    public function __construct(
        ConfigProvider $customerConfigProvider,
        GridConfigurationHelper $gridConfigurationHelper
    ) {
        $this->customerConfigProvider = $customerConfigProvider;
        $this->gridConfigurationHelper = $gridConfigurationHelper;
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
        $leadAlias        = $this->getEntityAlias($queryBuilder, $config);
        $customerAlias    = 'customer';
        $queryBuilder->join(sprintf('%s.customerAssociation', $leadAlias), $customerAlias);
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
     * @param DatagridConfiguration $config
     *
     * @return string|null
     */
    protected function getEntityClassName(DatagridConfiguration $config)
    {
        if ($this->entityClassName === null) {
            $this->entityClassName = $this->gridConfigurationHelper->getEntity($config);
        }

        return $this->entityClassName;
    }

    /**
     * @param QueryBuilder $qb
     * @param DatagridConfiguration $config
     *
     * @return string|null
     *
     * @throws DatasourceException
     */
    protected function getEntityAlias(QueryBuilder $qb, DatagridConfiguration $config)
    {
        $fromParts = $qb->getDQLPart('from');
        /** @var $fromPart Expr\From */
        foreach ($fromParts as $fromPart) {
            if ($fromPart->getFrom() === $this->getEntityClassName($config)) {
                return $fromPart->getAlias();
            }
        }

        throw new DatasourceException(sprintf(
            "Couldn't find %s alias in QueryBuilder.",
            $this->getEntityClassName($config)
        ));
    }

    /**
     * @param $customerClass
     *
     * @return string
     */
    protected function getCustomerField($customerClass)
    {
        return AccountCustomerManager::getCustomerTargetField($customerClass);
    }
}
