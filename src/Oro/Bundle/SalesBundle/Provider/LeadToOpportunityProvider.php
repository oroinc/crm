<?php

namespace Oro\Bundle\SalesBundle\Provider;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactAddress;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use Oro\Bundle\EntityBundle\Provider\EntityFieldProvider;
use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Model\ChangeLeadStatus;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Creates an Opportunity from the specified Lead.
 */
class LeadToOpportunityProvider implements LeadToOpportunityProviderInterface
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
            'firstName' => 'firstName',
            'jobTitle' => 'jobTitle',
            'lastName' => 'lastName',
            'middleName' => 'middleName',
            'namePrefix' => 'namePrefix',
            'nameSuffix' => 'nameSuffix',
            'twitter' => 'twitter',
            'linkedIn' => 'linkedIn',
            'owner' => 'owner',
            'source' => 'source'
        ],
        'extended_properties' => [
            'source' => 'enum'
        ]
    ];

    public function __construct(EntityFieldProvider $entityFieldProvider, ChangeLeadStatus $changeLeadStatus)
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->entityFieldProvider = $entityFieldProvider;
        $this->changeLeadStatus = $changeLeadStatus;
        $this->validateContactFields();
    }

    /**
     * @return array
     */
    protected function prepareEntityFields()
    {
        $rawFields = $this->entityFieldProvider->getEntityFields(
            Lead::class,
            EntityFieldProvider::OPTION_WITH_RELATIONS
            | EntityFieldProvider::OPTION_WITH_VIRTUAL_FIELDS
            | EntityFieldProvider::OPTION_APPLY_EXCLUSIONS
        );
        $fields = [];
        foreach ($rawFields as $field) {
            $fields[$field['name']] = $field['type'];
        }

        return $fields;
    }

    protected function validateContactFields()
    {
        $fields = $this->prepareEntityFields();
        foreach ($this->contactFields['extended_properties'] as $propertyName => $type) {
            $fieldValid = false;
            if (key_exists($propertyName, $fields) && $fields[$propertyName] !== $type) {
                $fieldValid = true;
            }

            if (!$fieldValid) {
                unset($this->contactFields['properties'][$propertyName]);
            }
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
            $propertyValue = is_array($value) ? $value['value'] : $this->accessor->getValue($sourceEntity, $value);
            if ($propertyValue) {
                $this->accessor->setValue($filledEntity, $key, $propertyValue);
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
     * {@inheritdoc}
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
     * {@inheritdoc}
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
