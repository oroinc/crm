<?php

namespace Oro\Bundle\SalesBundle\Migration\Extension;

/**
 * This trait can be used by migrations that implement {@see CustomerExtensionAwareInterface}.
 */
trait CustomerExtensionTrait
{
    private CustomerExtension $customerExtension;

    public function setCustomerExtension(CustomerExtension $customerExtension)
    {
        $this->customerExtension = $customerExtension;
    }
}
