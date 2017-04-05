<?php

namespace Oro\Bundle\SalesBundle\Provider;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactAddress;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use Oro\Bundle\EntityBundle\Provider\EntityFieldProvider;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Model\ChangeLeadStatus;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class LeadToOpportunityProvider
{
    /** @var PropertyAccessor */
    protected $accessor;

    /** @var ChangeLeadStatus */
    protected $changeLeadStatus;

    /** @var EntityFieldProvider */
    protected $entityFieldProvider;

    /**
     * @var array
     */
    protected $addressFields = [
        'properties' => [
            'firstName' => 'firstName',
            'lastName' => 'lastName',
            'middleName' => 'middleName',
            'namePrefix' => 'namePrefix',
            'nameSuffix' => 'nameSuffix',
            'city' => 'city',
            'country' => 'country',
            'label' => 'label',
            'organization' => 'organization',
            'postalCode' => 'postalCode',
            'region' => 'region',
            'regionText' => 'regionText',
            'street' => 'street',
            'street2' => 'street2',
            'primary' => 'primary'
        ]
    ];

    /**
     * @var array
     */
    protected $contactFields = [
        'properties' => [
        ],
        'excluded_proprieties' => [
            'id',
            'primaryEmail',
            'primaryAddr',
            'primaryPhone',
            'emails',
            'addresses',
            'phones',
            'owner_id',
            'method',
            'tags' ,
            'tag_field',
            'tags_virtual' ,
            'updated_by_id',
            'updatedBy',
            'updatedAt',
            'createdBy',
            'createdAt',
        ]
    ];

    /**
     * @param EntityFieldProvider $entityFieldProvider
     * @param ChangeLeadStatus $changeLeadStatus
     */
    public function __construct(EntityFieldProvider $entityFieldProvider, ChangeLeadStatus $changeLeadStatus)
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->entityFieldProvider = $entityFieldProvider;
        $this->changeLeadStatus = $changeLeadStatus;
        $this->validateContactFields();
    }

    /**
     *
     * @return array
     */
    protected function prepareEntityFields($entityName)
    {
        $rawFields = $this->entityFieldProvider->getFields(
            $entityName,
            true,
            true,
            false,
            false,
            true,
            true
        );

        $fields = [];
        foreach ($rawFields as $field) {
            $fields[$field['name']] = $field;
        }

        return $fields;
    }

    protected function validateContactFields()
    {
        $leadFields = $this->prepareEntityFields('OroSalesBundle:Lead');
        $contactFields = $this->prepareEntityFields('OroContactBundle:Contact');

        foreach ($contactFields  as $propertyName => $field) {

            if(in_array($propertyName , $this->contactFields['excluded_proprieties']))
                continue;

            if (! array_key_exists($propertyName, $leadFields))
                continue;

            if( $leadFields[$propertyName]['type'] !== $contactFields[$propertyName]['type'])
                continue;

            if(in_array($leadFields[$propertyName]['type'] , [
                'ref-one', 'ref-many',
                'manyToOne', 'oneToMany',
                'oneToOne', 'ManyToMany',])) {

                if( $leadFields[$propertyName]['relation_type'] !== $contactFields[$propertyName]['relation_type'])
                    continue;

                if ($leadFields[$propertyName]['related_entity_name'] !== $contactFields[$propertyName]['related_entity_name'])
                    continue;
            }

            if(in_array($leadFields[$propertyName]['type'] , [ 'enum', 'multiEnum' ])) {

                if( $leadFields[$propertyName]['relation_type'] !== $contactFields[$propertyName]['relation_type'])
                    continue;

                    // @todo check if enum value exists in both types

            }

            $this->contactFields['properties'][$propertyName] = $propertyName;
        }
    }

    /**
     * @param object $filledEntity
     * @param array $properties
     * @param object $sourceEntity
     */
    protected function fillEntityProperties($filledEntity, array $properties, $sourceEntity)
    {
        foreach ($properties as $key => $value) {

            try
            {
                $propertyValue = is_array($value) ? $value['value'] : $this->accessor->getValue($sourceEntity, $value);

                if ($propertyValue)
                {
                    $this->accessor->setValue($filledEntity, $key, $propertyValue);
                }
            } catch (AccessException $e) {

            }

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
            $contact = new Contact();

            $this->fillEntityProperties(
                $contact,
                $this->contactFields['properties'],
                $lead
            );

            $emails = $lead->getEmails();
            foreach ($emails as $email) {
                $contactEmail = new ContactEmail($email->getEmail());
                $contactEmail->setPrimary($email->isPrimary());
                $contact->addEmail($contactEmail);
            }

            $phones = $lead->getPhones();
            foreach ($phones as $phone) {
                $contactPhone = new ContactPhone($phone->getPhone());
                $contactPhone->setPrimary($phone->isPrimary());
                $contact->addPhone($contactPhone);
            }

            $addresses = $lead->getAddresses();
            foreach ($addresses as $address) {
                $contactAddress = new ContactAddress();
                $contactAddress->setPrimary($address->isPrimary());
                $this->fillEntityProperties(
                    $contactAddress,
                    $this->addressFields['properties'],
                    $address
                );
                $contact->addAddress($contactAddress);
            }
        }

        return $contact;
    }

    /**
     * @param Lead $lead
     * @param bool $isGetRequest
     *
     * @return Opportunity
     */
    public function prepareOpportunityForForm(Lead $lead, $isGetRequest = true)
    {
        $opportunity = $this->createOpportunity();
        $opportunity->setLead($lead);

        if ($isGetRequest) {
            $contact = $this->prepareContactToOpportunity($lead);
            $opportunity
                ->setContact($contact)
                ->setName($lead->getName());

            $opportunity->setCustomerAssociation($lead->getCustomerAssociation());
        } else {
            $opportunity
                // Set predefined contact entity to have proper validation
                // of addresses sub-form in case when user submit empty address
                ->setContact(new Contact());
        }

        return $opportunity;
    }

    /**
     * @param Opportunity $opportunity
     * @param callable    $errorMessageCallback
     *
     * @return bool
     */
    public function saveOpportunity(Opportunity $opportunity, callable $errorMessageCallback)
    {
        $lead = $opportunity->getLead();
        $this->setContactAndAccountToLeadFromOpportunity($lead, $opportunity);

        $saveResult = $this->changeLeadStatus->qualify($lead);

        if (!$saveResult && is_callable($errorMessageCallback)) {
            call_user_func($errorMessageCallback);
        }

        return $saveResult;
    }

    /**
     * @param Lead        $lead
     * @param Opportunity $opportunity
     */
    protected function setContactAndAccountToLeadFromOpportunity(Lead $lead, Opportunity $opportunity)
    {
        $lead->setContact($opportunity->getContact());
    }

    /**
     * @return Opportunity
     */
    protected function createOpportunity()
    {
        return new Opportunity();
    }
}
