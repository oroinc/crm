<?php

namespace Oro\Bundle\SalesBundle\Migration\Extension\Customers;

/**
 * LeadExtensionAwareInterface should be implemented by migrations that depends
 * on a Customers/LeadExtension.
 */
interface LeadExtensionAwareInterface
{
    /**
     * Sets the LeadExtension
     *
     * @param LeadExtension $leadExtension
     */
    public function setLeadExtension(LeadExtension $leadExtension);
}
