<?php

namespace OroCRM\Bundle\SalesBundle\Provider;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\ContactBundle\Entity\Contact;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LeadToOpportunityProvider
{
    /**
     * @param Lead $lead
     * @return Opportunity
     */
    public function convertLeadToOpportunity(Lead $lead)
    {
        $leadStatus = $lead->getStatus()->getName();

        if ( $leadStatus !== 'new' ) {
            throw new HttpException(403, 'Not allowed action');
        }

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

    /**
     * @param Lead $lead
     *
     * @return string
     */
    public function getFormIdByLead(Lead $lead)
    {
        $contact = $lead->getContact();
        return $this->getFormId(is_null($contact));
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    public function getFormIdByRequest(Request $request)
    {
        return $this->getFormId($request->query->get('use_full_contact_form'));
    }

    /**
     * Get convertation form id
     *
     * @param bool $withFullContactForm
     *
     * @return string
     */
    protected function getFormId($withFullContactForm)
    {
        return $withFullContactForm ?
            'orocrm_sales.lead_to_opportunity_with_subform.form':
            'orocrm_sales.lead_to_opportunity.form' ;
    }
}
