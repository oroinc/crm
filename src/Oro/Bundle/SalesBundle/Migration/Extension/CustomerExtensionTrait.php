<?php

namespace Oro\Bundle\SalesBundle\Migration\Extension;

trait CustomerExtensionTrait
{
    /** @var CustomerExtension */
    protected $customerExtension;

    /**
     * @param CustomerExtension $customerExtension
     */
    public function setCustomerExtension(CustomerExtension $customerExtension)
    {
        $this->customerExtension = $customerExtension;
    }
}
