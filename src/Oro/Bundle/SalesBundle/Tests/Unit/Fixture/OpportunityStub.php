<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Fixture;

use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

class OpportunityStub extends Opportunity
{
    /** @var object|null */
    protected $customerTarget;

    /** @var object|null */
    protected $dataChannel;

    /** @var AbstractEnumValue */
    private $status;

    /**
     * @inheritDoc
     */
    public function __construct($id = null)
    {
        parent::__construct();

        $this->id = $id;
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

    public function setStatus(AbstractEnumValue $status)
    {
        $this->status = $status;
    }

    /**
     * @return AbstractEnumValue
     */
    public function getStatus()
    {
        return $this->status;
    }
}
