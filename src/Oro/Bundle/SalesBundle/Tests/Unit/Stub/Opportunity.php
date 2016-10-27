<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Stub;

use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\SalesBundle\Entity\Opportunity as BaseOpportunity;

class Opportunity extends BaseOpportunity
{
    /** @var AbstractEnumValue $status */
    private $status;

    /** @var Customer1 */
    protected $customer1;

    /** @var Customer2 */
    protected $customer2;

    /**
     * @param AbstractEnumValue $status
     */
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

    /**
     * @return Customer1|null
     */
    public function getCustomer1()
    {
        return $this->customer1;
    }

    /**
     * @return Customer2|null
     */
    public function getCustomer2()
    {
        return $this->customer2;
    }

    /**
     * @param Customer1|null $customer1
     *
     * @return Opportunity
     */
    public function setCustomer1(Customer1 $customer1 = null)
    {
        $this->customer1 = $customer1;

        return $this;
    }

    /**
     * @param Customer2|null $customer2
     *
     * @return Opportunity
     */
    public function setCustomer2(Customer2 $customer2 = null)
    {
        $this->customer2 = $customer2;

        return $this;
    }
}
