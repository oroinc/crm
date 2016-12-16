<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Fixture;

use Oro\Bundle\SalesBundle\Entity\Opportunity;

class OpportunityStub extends Opportunity
{
    /** @var object|null */
    protected $customerTarget;

    /** @var object|null */
    protected $dataChannel;

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

    /**
     * @return object|null
     */
    public function getDataChannel()
    {
        return $this->dataChannel;
    }

    /**
     * @param object|null $dataChannel
     */
    public function setDataChannel($dataChannel)
    {
        $this->dataChannel = $dataChannel;
    }
}
