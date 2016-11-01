<?php

namespace Oro\Bundle\SalesBundle\Provider\Customers;

interface SalesItemsProviderInterface
{
    public function supportCustomer($customerClass);

    public function supportCustomerSalesItems($customerClass, $salesItemClass);
}
