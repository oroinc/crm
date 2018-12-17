<?php

namespace Oro\Bundle\SalesBundle\Provider\Customer;

use Oro\Bundle\AccountBundle\Entity\Account;

/**
 * Provides the same information as the default customer configuration provider,
 * but adds Account entity to the list of customer classes.
 */
class AccountConfigProvider extends ConfigProvider
{
    /**
     * {@inheritdoc}
     */
    public function getAssociatedCustomerClasses()
    {
        return array_merge([Account::class], parent::getAssociatedCustomerClasses());
    }

    /**
     * @return string
     */
    protected function getCacheKey()
    {
        return 'account';
    }
}
