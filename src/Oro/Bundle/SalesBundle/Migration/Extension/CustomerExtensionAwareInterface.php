<?php

namespace Oro\Bundle\SalesBundle\Migration\Extension;

/**
 * This interface should be implemented by migrations that depend on {@see CustomerExtension}.
 */
interface CustomerExtensionAwareInterface
{
    public function setCustomerExtension(CustomerExtension $leadExtension);
}
