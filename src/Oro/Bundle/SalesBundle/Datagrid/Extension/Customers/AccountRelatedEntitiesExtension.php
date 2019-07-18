<?php

namespace Oro\Bundle\SalesBundle\Datagrid\Extension\Customers;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;

/**
 * Restrict related entities grid by account.
 */
class AccountRelatedEntitiesExtension extends RelatedEntitiesExtension
{
    /**
     * {@inheritdoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        return
            AbstractExtension::isApplicable($config)
            && $config->isOrmDatasource()
            && $this->parameters->get('customer_id')
            && $this->parameters->get('customer_class') === Account::class
            && $this->parameters->get('related_entity_class') === $this->relatedEntityClass;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCustomerField($customerClass)
    {
        return 'account';
    }
}
