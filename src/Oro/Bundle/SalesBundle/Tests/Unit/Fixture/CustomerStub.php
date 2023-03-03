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

    /**
     * {@inheritDoc}
     */
    public function getCustomerTarget()
    {
        return $this->customerTarget;
    }

    /**
     * {@inheritDoc}
     */
    public function setCustomerTarget($target)
    {
        $this->customerTarget = $target;

        return $this;
    }
}
