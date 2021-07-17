<?php

namespace Oro\Bundle\SalesBundle\Migration\Extension;

/**
 * CustomerExtensionAwareInterface should be implemented by migrations that depends on CustomerExtension
 */
interface CustomerExtensionAwareInterface
{
    /**
     * Sets the LeadExtension
     */
    public function setCustomerExtension(CustomerExtension $leadExtension);
}
