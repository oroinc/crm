<?php

namespace Oro\Bundle\SalesBundle\Provider\Customer;

use Oro\Bundle\AccountBundle\Entity\Account;

class AccountCustomerConfigProvider extends CustomerConfigProvider
{
    /**
     * {@inheritdoc}
     */
    public function getAssociatedCustomerClasses()
    {
        return array_merge([Account::class], parent::getAssociatedCustomerClasses());
    }
}
