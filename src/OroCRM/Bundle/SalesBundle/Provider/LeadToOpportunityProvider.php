<?php

namespace OroCRM\Bundle\SalesBundle\Provider;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\ContactBundle\Entity\Contact;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LeadToOpportunityProvider
{
    protected function validateLeadStatus(Lead $lead)
    {
        $leadStatus = $lead->getStatus()->getName();

        if ( $leadStatus !== 'new' ) {
            throw new HttpException(403, 'Not allowed action');
        }
    }

    /**
     * @param Lead $lead
     * @return Opportunity
     */
    public function prepareOpportunity(Lead $lead, Request $request)
    {
        $opportunity = new Opportunity();
        $opportunity->setLead($lead);

        if ($request->getMethod() === 'GET') {
            $this->validateLeadStatus($lead);
            $contact = $lead->getContact() instanceof Contact ? $lead->getContact() : new Contact();
            $opportunity
                ->setName($lead->getName())
                ->setContact($contact);
            if ($customer = $lead->getCustomer()) {
                $opportunity->setCustomer($customer);
            }
        }

        return $opportunity;
    }

    /**
     * @param Lead $lead
     *
     * @return string
     */
    public function getFormId(Lead $lead)
    {
        $contact = $lead->getContact();
        return is_null($contact) ?
            'orocrm_sales.lead_to_opportunity_with_subform.form':
            'orocrm_sales.lead_to_opportunity.form';
    }
}
