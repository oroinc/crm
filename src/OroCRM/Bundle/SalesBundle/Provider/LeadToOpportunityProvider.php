<?php

namespace OroCRM\Bundle\SalesBundle\Provider;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\SalesBundle\Model\B2bGuesser;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;
use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PropertyAccess\PropertyAccess;

class LeadToOpportunityProvider
{
    protected $accessor;

    protected $b2bGuesser;

    protected $addressFields = [
        'properties' => [
            'city' => 'city',
            'country' => 'country',
            'label' => 'label',
            'organization' => 'organization',
            'postalCode' => 'postalCode',
            'region' => 'region',
            'regionText' => 'regionText',
            'street' => 'street',
            'street2' => 'street2',
            'primary' => array(
                'value' => true
            )
        ],
        'entity' => 'OroCRM\Bundle\ContactBundle\Entity\ContactAddress'
    ];

    protected $contactFields = [
        'properties' => [
            'firstName' => 'firstName',
            'jobTitle' => 'jobTitle',
            'lastName' => 'lastName',
            'middleName' => 'middleName',
            'namePrefix' => 'namePrefix',
            'nameSuffix' => 'nameSuffix',
            'owner' => 'owner',
            'source' => 'source'
        ],
        'methods' => [
            'addEmail' => 'email',
            'addPhone' => 'phoneNumber',
            'addAddress' => [
                'entity' => 'address',
                'merge_fields' => [
                    'firstName', 'lastName', 'middleName', 'namePrefix', 'nameSuffix'
                ]
            ]
        ],
        'entity' => 'OroCRM\Bundle\ContactBundle\Entity\Contact'
    ];

    public function __construct(B2bGuesser $b2bGuesser)
    {
        $this->b2bGuesser = $b2bGuesser;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

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
     * @param object $filledEntity
     * @param array $properties
     * @param Lead $sourceEntity
     */
    protected function fillEntityProperties($filledEntity, array $properties, $sourceEntity)
    {
        foreach ($properties as $key => $value) {
            $propertyValue = is_array($value) ? $value['value'] : $this->accessor->getValue($sourceEntity, $value);
            if ($propertyValue) {
                $this->accessor->setValue($filledEntity, $key, $propertyValue);
            }
        }
    }

    /**
     * @param $entity
     * @param $methodName
     * @param $value
     */
    protected function resolveMethod($entity, $methodName, $value)
    {
        switch ($methodName) {
            case 'addEmail':
                $entity->$methodName(new ContactEmail($value));
                break;
            case 'addPhone':
                $entity->$methodName(new ContactPhone($value));
                break;
            case 'addAddress':
                $entity->$methodName($value);
                break;
        }
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
            $contact = new $this->contactFields['entity']();

            $this->fillEntityProperties(
                $contact,
                $this->contactFields['properties'],
                $lead
            );

            foreach ($this->contactFields['methods'] as $method => $value) {
                $propertyValue = null;
                if (is_array($value)) {
                    $subEntity = $this->accessor->getValue($lead, $value['entity']);
                    if (is_object($subEntity) && $value['entity'] === 'address') {
                        $propertyValue = new $this->addressFields['entity']();

                        $this->fillEntityProperties(
                            $propertyValue,
                            $this->addressFields['properties'],
                            $subEntity
                        );

                        $leadFields = array_intersect_key(
                            $this->contactFields['properties'],
                            array_flip($value['merge_fields'])
                        );
                        $this->fillEntityProperties(
                            $propertyValue,
                            $leadFields,
                            $lead
                        );
                    }
                } else {
                    $propertyValue = $this->accessor->getValue($lead, $value);
                }

                if ($propertyValue) {
                    $this->resolveMethod($contact, $method, $propertyValue);
                }
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
        $opportunity->setLead($lead);

        if ($request->getMethod() === 'GET' && $this->validateLeadStatus($lead)) {
            $contact = $this->prepareContactToOpportunity($lead);
            $opportunity
                ->setContact($contact)
                ->setName($lead->getName());

            $opportunity->setCustomer($this->b2bGuesser->getCustomer($lead));

        } else {
            $opportunity
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
