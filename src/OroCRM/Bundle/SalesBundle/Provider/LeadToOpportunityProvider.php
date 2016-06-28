<?php

namespace OroCRM\Bundle\SalesBundle\Provider;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use OroCRM\Bundle\SalesBundle\Entity\Lead;

class LeadToOpportunityProvider
{
    /**
     * @param Lead $lead
     * @return Opportunity
     */
    public function convertToOpportunityEntity(Lead $lead)
    {
        $opportunity = new Opportunity();
        $opportunity->setLead($lead);

        if ($contact = $lead->getContact()) {
            $opportunity->setContact($contact);
        }

        return $opportunity;
    }
}
