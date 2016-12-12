<?php

namespace Oro\Bundle\SalesBundle\Datagrid\Extension\Customers;

use Doctrine\ORM\Query\Expr;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;

use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider;

use Oro\Bundle\DataGridBundle\Tools\GridConfigurationHelper;

use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\AccountBundle\Entity\Account;

class AccountExtension extends AbstractExtension
{
    const COLUMN_NAME = 'associatedAccountName';

    const CUSTOMER_ROOT_PARAM = '_customers';
    const DISABLED_PARAM      = '_disabled';

    /** @var ConfigProvider */
    protected $customerConfigProvider;

    /** @var GridConfigurationHelper */
    protected $gridConfigurationHelper;

    protected $entityClassName;

    /**
     * @param ConfigProvider          $customerConfigProvider
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
            $this->customerConfigProvider->isCustomerClass($this->getEntity($config)) &&
            $this->gridConfigurationHelper->getEntityRootAlias($config) &&
            !$this->isReportOrSegmentGrid($config) &&
            !$this->isDisabled();
    }

    /**
     * Checks if configuration is for report or segment grid
     *
     * @param DatagridConfiguration $config
     *
     * @return bool
     */
    protected function isReportOrSegmentGrid(DatagridConfiguration $config)
    {
        $gridName = $config->getName();

        return
            strpos($gridName, 'oro_report') === 0 ||
            strpos($gridName, 'oro_segment') === 0;
    }

    /**
     * @return bool
     */
    protected function isDisabled()
    {
        $parameters = $this->getParameters()->get(self::CUSTOMER_ROOT_PARAM);

        return
            $parameters &&
            !empty($parameters[self::DISABLED_PARAM]);
    }

    /**
     * {@inheritdoc}
     */
    public function processConfigs(DatagridConfiguration $config)
    {
        $columns = $config->offsetGetByPath('[columns]', []);
        $column = [self::COLUMN_NAME => $this->getColumnDefinition()];
        $config->offsetSetByPath('[columns]', array_merge($columns, $column));

        $filters = $config->offsetGetByPath('[filters][columns]', []);
        if (!empty($filters)) {
            $filters[self::COLUMN_NAME] = $this->getColumnFilterDefinition();
            $config->offsetSetByPath('[filters][columns]', $filters);
        }

        $sorters = $config->offsetGetByPath('[sorters][columns]', []);
        if (!empty($sorters)) {
            $sorters[self::COLUMN_NAME] = $this->getColumnSortDefinition();
            $config->offsetSetByPath('[sorters][columns]', $sorters);
        }
    }

    protected function getColumnDefinition()
    {
        return [
            'label' => $this->customerConfigProvider->getLabel(Account::class),
        ];
    }

    protected function getColumnFilterDefinition()
    {
        return [
            'type'      => 'string',
            'data_name' => static::COLUMN_NAME,
        ];
    }

    protected function getColumnSortDefinition()
    {
        return [
            'data_name' => static::COLUMN_NAME,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
        /** @var $datasource OrmDataSource */
        $customerClass = $this->getEntity($config);
        $customerField = $this->getCustomerField($customerClass);
        $queryBuilder = $datasource->getQueryBuilder();

        $rootAlias = $this->gridConfigurationHelper->getEntityRootAlias($config);

        $queryBuilder->leftJoin(
            Customer::class,
            'customerAssociation',
            'WITH',
            sprintf('customerAssociation.%s = %s', $customerField, $rootAlias)
        );
        $queryBuilder->leftJoin('customerAssociation.account', 'associatedAccount');
        $queryBuilder->addSelect(sprintf('associatedAccount.name as %s', static::COLUMN_NAME));
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

    /**
     * @param DatagridConfiguration $config
     *
     * @return string|null
     */
    protected function getEntity(DatagridConfiguration $config)
    {
        if ($this->entityClassName === null) {
            $this->entityClassName = $this->gridConfigurationHelper->getEntity($config);
        }

        return $this->entityClassName;
    }
}
