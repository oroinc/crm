<?php

namespace OroCRM\Bundle\SalesBundle\Provider;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\ContactBundle\Entity\Contact;

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
        $contact = $lead->getContact() instanceof Contact ? $lead->getContact() : new Contact();
        $opportunity
            ->setName($lead->getName())
            ->setContact($contact);
        if ($customer = $lead->getCustomer()) {
            $opportunity->setCustomer($customer);
        }

        return $opportunity;
    }
}
