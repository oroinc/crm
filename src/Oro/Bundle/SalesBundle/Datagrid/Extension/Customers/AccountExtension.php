<?php

namespace Oro\Bundle\SalesBundle\Datagrid\Extension\Customers;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\EntityBundle\ORM\EntityClassResolver;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider;
use Oro\Bundle\AccountBundle\Entity\Account;

class AccountExtension extends AbstractExtension
{
    const COLUMN_NAME = 'associatedAccountName';

    const CUSTOMER_ROOT_PARAM = '_customers';
    const DISABLED_PARAM      = '_disabled';

    /** @var ConfigProvider */
    protected $customerConfigProvider;

    /** @var EntityClassResolver */
    protected $entityClassResolver;

    protected $entityClassName;

    /**
     * @param ConfigProvider      $customerConfigProvider
     * @param EntityClassResolver $entityClassResolver
     */
    public function __construct(
        ConfigProvider $customerConfigProvider,
        EntityClassResolver $entityClassResolver
    ) {
        $this->customerConfigProvider = $customerConfigProvider;
        $this->entityClassResolver = $entityClassResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        return
            $config->isOrmDatasource()
            && !$this->isDisabled()
            && !$this->isReportOrSegmentGrid($config)
            && $config->getOrmQuery()->getRootAlias()
            && $this->customerConfigProvider->isCustomerClass($this->getEntity($config));
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
            0 === strpos($gridName, 'oro_report')
            || 0 === strpos($gridName, 'oro_segment');
    }

    /**
     * @return bool
     */
    protected function isDisabled()
    {
        $parameters = $this->getParameters()->get(self::CUSTOMER_ROOT_PARAM);

        return
            $parameters
            && !empty($parameters[self::DISABLED_PARAM]);
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
            'label'         => $this->customerConfigProvider->getLabel(Account::class),
            'type'          => 'field',
            'frontend_type' => 'string',
            'translatable'  => true,
            'editable'      => false,
            'renderable'    => true,
        ];
    }

    protected function getColumnFilterDefinition()
    {
        return [
            'type'         => 'string',
            'data_name'    => static::COLUMN_NAME,
            'translatable' => true,
            'enabled'      => true,
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
        /** @var OrmDatasource $datasource */
        $customerClass = $this->getEntity($config);
        $customerField = $this->getCustomerField($customerClass);
        $queryBuilder = $datasource->getQueryBuilder();

        $queryBuilder->leftJoin(
            Customer::class,
            'customerAssociation',
            'WITH',
            sprintf('customerAssociation.%s = %s', $customerField, $config->getOrmQuery()->getRootAlias())
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
            $this->entityClassName = $config->getOrmQuery()->getRootEntity($this->entityClassResolver, true);
        }

        return $this->entityClassName;
    }
}
