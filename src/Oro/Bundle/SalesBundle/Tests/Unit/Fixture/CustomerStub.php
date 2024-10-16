<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Fixture;

use Oro\Bundle\SalesBundle\Entity\Customer;

class CustomerStub extends Customer
{
    private ?object $customerTarget = null;

    public function __construct($id = null)
    {
        $this->id = $id;
    }

    public function getCustomerTarget()
    {
        return $this->customerTarget;
    }

    public function setCustomerTarget($target)
    {
        $this->customerTarget = $target;

        return $this;
    }
}
