<?php

namespace Oro\Bundle\SalesBundle\Migration\Extension\Customers;

/**
 * OpportunityExtensionAwareInterface should be implemented by migrations that depends on OpportunityExtension
 */
interface OpportunityExtensionAwareInterface
{
    /**
     * Sets the OpportunityExtension
     *
     * @param OpportunityExtension $opportunityExtension
     */
    public function setOpportunityExtension(OpportunityExtension $opportunityExtension);
}
