<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Stub;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager as BaseAccountCustomerManager;
use Oro\Bundle\SalesBundle\Tests\Unit\Fixture\CustomerStub as Customer;

class AccountCustomerManager extends BaseAccountCustomerManager
{
    /**
     * {@inheritdoc}
     */
    public static function createCustomer(Account $account, $target = null)
    {
        $customer = new Customer();

        return $customer->setTarget($account, $target);
    }
}
