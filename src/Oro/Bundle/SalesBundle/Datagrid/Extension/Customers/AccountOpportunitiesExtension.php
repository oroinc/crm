<?php

namespace Oro\Bundle\SalesBundle\Datagrid\Extension\Customers;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;

class AccountOpportunitiesExtension extends OpportunitiesExtension
{
    /**
     *{@inheritdoc}
     */
    protected function getCustomerField($customerClass)
    {
        return 'account';
    }

    /**
     *{@inheritdoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        return
            $config->getDatasourceType() === OrmDatasource::TYPE &&
            $this->parameters->get('customer_class') &&
            $this->parameters->get('customer_id') &&
            $this->customerConfigProvider->isCustomerClass($this->parameters->get('customer_class'));
    }
}
