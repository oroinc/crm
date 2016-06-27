<?php

namespace OroCRM\Bundle\SalesBundle\Provider;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use OroCRM\Bundle\SalesBundle\Entity\Lead;


class LeadToOpportunityProvider
{
    /**
     * @var Lead
     */
    protected $lead;

    /**
     * @param Lead $lead
     * @return LeadToOpportunityProvider
     */
    public function setLead(Lead $lead)
    {
        $this->lead = $lead;

        return $this;
    }

    /**
     * @return Opportunity
     */
    public function getOpportunityEntity()
    {
        $opportunity = new Opportunity();
        $opportunity->setLead($this->lead);

        if ($contact = $this->lead->getContact()) {
            $opportunity->setContact($contact);
        }

        return $opportunity;
    }
}
