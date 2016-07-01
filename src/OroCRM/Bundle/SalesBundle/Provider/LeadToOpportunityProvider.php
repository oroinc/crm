<?php

namespace OroCRM\Bundle\SalesBundle\Provider;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;
use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LeadToOpportunityProvider
{
    /**
     * @param Lead $lead
     *
     * @return bool
     */
    protected function validateLeadStatus(Lead $lead)
    {
        $leadStatus = $lead->getStatus()->getName();

        if ($leadStatus !== 'new') {
            throw new HttpException(403, 'Not allowed action');
        }

        return true;
    }

    /**
     * @param Lead $lead
     *
     * @return Contact
     */
    protected function prepareContactToOpportunity(Lead $lead)
    {
        $contact = $lead->getContact();

        if (!$contact instanceof Contact) {
            $contact = new Contact();
            $leadFields = [
                'source',
                'owner',
                'namePrefix',
                'firstName',
                'middleName',
                'lastName',
                'nameSuffix',
                'jobTitle'
            ];

            foreach ($leadFields as $field) {
                $contact->{'set' . ucfirst($field)}($lead->{'get' . ucfirst($field)}());
            }

            if ($lead->getEmail()) {
                $contact->addEmail(new ContactEmail($lead->getEmail()));
            }

            if ($lead->getPhoneNumber()) {
                $contact->addPhone(new ContactPhone($lead->getPhoneNumber()));
            }

            if ($lead->getAddress()) {
                $addressFields = [
                    'label',
                    'street2',
                    'region',
                    'country',
                    'street',
                    'postalCode',
                    'city',
                    'firstName',
                    'middleName',
                    'lastName',
                    'nameSuffix',
                    'organization',
                    'namePrefix'
                ];

                $contactAddress = new ContactAddress();
                $leadAddress = $lead->getAddress();
                foreach ($addressFields as $field) {
                    $contactAddress->{'set' . ucfirst($field)}($leadAddress->{'get' . ucfirst($field)}());
                }
                $contactAddress->setPrimary(true);
                $contact->addAddress($contactAddress);
            }
        }

        return $contact;
    }

    /**
     * @param Lead $lead
     * @return Opportunity
     */
    public function prepareOpportunity(Lead $lead, Request $request)
    {
        $opportunity = new Opportunity();

        if ($request->getMethod() === 'GET' && $this->validateLeadStatus($lead)) {
            $contact = $this->prepareContactToOpportunity($lead);
            $opportunity
                ->setContact($contact)
                ->setName($lead->getName());
            
            if ($customer = $lead->getCustomer()) {
                $opportunity->setCustomer($customer);
            }
        } else {
            $opportunity
                ->setLead($lead)
                // set predefined contact entity to have proper validation
                ->setContact(new Contact());
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
