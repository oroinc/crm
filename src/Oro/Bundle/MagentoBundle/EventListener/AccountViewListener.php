<?php

namespace Oro\Bundle\MagentoBundle\EventListener;

use Oro\Bundle\SalesBundle\EventListener\AccountViewListener as BaseAccountViewListener;

class AccountViewListener extends BaseAccountViewListener
{
    /**
     * {@inheritdoc}
     */
    protected function getCustomerAssociation($customer, $throwExceptionOnNotFound = true)
    {
        $throwExceptionOnNotFound = false;

        return parent::getCustomerAssociation($customer, $throwExceptionOnNotFound);
    }
}
