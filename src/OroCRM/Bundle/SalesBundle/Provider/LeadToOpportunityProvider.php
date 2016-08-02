<?php

namespace OroCRM\Bundle\SalesBundle\Provider;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

use Oro\Bundle\EntityBundle\Provider\EntityFieldProvider;
use Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\SalesBundle\Model\B2bGuesser;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;
use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use OroCRM\Bundle\SalesBundle\Model\ChangeLeadStatus;

class LeadToOpportunityProvider
{
    /**
     * @var PropertyAccessor
     */
    protected $accessor;

    /**
     * @var B2bGuesser
     */
    protected $b2bGuesser;

    /**
     * @var ChangeLeadStatus
     */
    protected $changeLeadStatus;

    /** @var bool */
    protected $isLeadWorkflowEnabled;

    /**
     * @var EntityFieldProvider
     */
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

    /**
     * @param B2bGuesser $b2bGuesser
     * @param EntityFieldProvider $entityFieldProvider
     * @param ChangeLeadStatus $changeLeadStatus
     * @param WorkflowRegistry $workflowRegistry
     */
    public function __construct(
        B2bGuesser $b2bGuesser,
        EntityFieldProvider $entityFieldProvider,
        ChangeLeadStatus $changeLeadStatus,
        WorkflowRegistry $workflowRegistry
    ) {
        $this->b2bGuesser = $b2bGuesser;
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->entityFieldProvider = $entityFieldProvider;
        $this->changeLeadStatus = $changeLeadStatus;
        $this->validateContactFields();
        $this->isLeadWorkflowEnabled = $workflowRegistry
            ->hasActiveWorkflowsByEntityClass('OroCRM\Bundle\SalesBundle\Entity\Lead');
    }

    /**
     * @return array
     */
    protected function prepareEntityFields()
    {
        $rawFields = $this->entityFieldProvider->getFields(
            'OroCRMSalesBundle:Lead',
            true,
            true,
            false,
            false,
            true,
            true
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
     * @param Lead $lead
     * @param bool $isGetRequest
     *
     * @return Opportunity
     */
    public function prepareOpportunityForForm(Lead $lead, $isGetRequest = true)
    {
        $opportunity = new Opportunity();
        $opportunity->setLead($lead);

        if ($isGetRequest) {
            $contact = $this->prepareContactToOpportunity($lead);
            $opportunity
                ->setContact($contact)
                ->setName($lead->getName());

            $this->b2bGuesser->setCustomer($opportunity, $lead);

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
        $customer = $opportunity->getCustomer();

        $this->setContactAndAccountToLeadFromOpportunity($lead, $opportunity);
        $this->prepareCustomerToSave($customer, $opportunity);

        $saveResult = $this->changeLeadStatus->qualify($lead);

        if (!$saveResult && is_callable($errorMessageCallback)) {
            call_user_func($errorMessageCallback);
        }

        return $saveResult;
    }

    /**
     * @param B2bCustomer $customer
     * @param Opportunity $opportunity
     */
    protected function prepareCustomerToSave(B2bCustomer $customer, Opportunity $opportunity)
    {
        $contact = $opportunity->getContact();
        if (!$customer->getContact() instanceof Contact) {
            $customer->setContact($contact);
        }

        if ($customer->getAccount() instanceof Account) {
            $customer->getAccount()->addContact($contact);
        }
    }

    /**
     * @param Lead        $lead
     * @param Opportunity $opportunity
     */
    protected function setContactAndAccountToLeadFromOpportunity(Lead $lead, Opportunity $opportunity)
    {
        $lead->setContact($opportunity->getContact());
        $lead->setCustomer($opportunity->getCustomer());
    }

    /**
     * @param Lead $lead
     *
     * @return bool
     */
    public function isDisqualifyAndConvertAllowed(Lead $lead)
    {
        return $lead->getStatus()->getId() !== ChangeLeadStatus::STATUS_DISQUALIFY &&
        !$this->isLeadWorkflowEnabled &&
        $lead->getOpportunities()->count() === 0;
    }
}
