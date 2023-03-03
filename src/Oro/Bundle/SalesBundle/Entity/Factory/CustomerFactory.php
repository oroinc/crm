<?php

namespace Oro\Bundle\SalesBundle\Entity\Factory;

use Oro\Bundle\SalesBundle\Entity\Customer;

/**
 * The factory to create instance of customer class.
 */
class CustomerFactory
{
    public function createCustomer(): Customer
    {
        return new Customer();
    }
}
