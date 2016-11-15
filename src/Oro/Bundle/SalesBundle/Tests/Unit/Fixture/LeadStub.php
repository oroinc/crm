<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Fixture;

use Oro\Bundle\SalesBundle\Entity\Lead;

class LeadStub extends Lead
{
    /**
     * @var object
     */
    protected $source;

    /**
     * @var object
     */
    protected $status;

    /**
     * @var object|null
     */
    protected $customerTarget;

    /**
     * @return object
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param object $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return object
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param object $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

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
