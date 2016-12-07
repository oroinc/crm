<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Fixture;

use Oro\Bundle\SalesBundle\Entity\Opportunity;

class OpportunityStub extends Opportunity
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
     */
    public function setCustomerTarget($customerTarget)
    {
        $this->customerTarget = $customerTarget;
    }
}
