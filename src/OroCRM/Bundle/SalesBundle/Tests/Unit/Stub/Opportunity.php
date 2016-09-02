<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Stub;

use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity as BaseOpportunity;

class Opportunity extends BaseOpportunity
{
    /** @var AbstractEnumValue $status */
    private $status;

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
}
