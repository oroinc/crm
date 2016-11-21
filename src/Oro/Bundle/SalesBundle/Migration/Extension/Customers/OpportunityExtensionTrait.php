<?php

namespace Oro\Bundle\SalesBundle\Migration\Extension\Customers;

trait OpportunityExtensionTrait
{
    /** @var OpportunityExtension */
    protected $opportunityExtension;

    /**
     * @param OpportunityExtension $opportunityExtension
     */
    public function setOpportunityExtension(OpportunityExtension $opportunityExtension)
    {
        $this->opportunityExtension = $opportunityExtension;
    }
}
