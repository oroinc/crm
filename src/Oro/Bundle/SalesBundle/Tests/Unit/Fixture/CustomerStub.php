<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Fixture;

use Oro\Bundle\SalesBundle\Entity\Customer;

class CustomerStub extends Customer
{
    /** @var object|null */
    protected $customerTarget;

    /**
     * @return object|null
     */
    public function getCustomerTarget()
    {
        return $this->customerTarget;
    }

    /**
     * @param object|null $customerTarget
     *
     * @return CustomerStub
     */
    public function setCustomerTarget($customerTarget)
    {
        $this->customerTarget = $customerTarget;

        return $this;
    }
}
