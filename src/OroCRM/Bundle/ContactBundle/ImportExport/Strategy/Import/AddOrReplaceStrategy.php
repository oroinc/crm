<?php

namespace OroCRM\Bundle\ContactBundle\ImportExport\Strategy\Import;

use Oro\Bundle\ImportExportBundle\Exception\InvalidArgumentException;
use Oro\Bundle\ImportExportBundle\Exception\LogicException;
use Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper;
use Oro\Bundle\ImportExportBundle\Context\ContextAwareInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\ImportExport\Strategy\Import\ContactImportStrategyHelper;

class AddOrReplaceStrategy implements StrategyInterface, ContextAwareInterface
{
    /**
     * @var ImportStrategyHelper
     */
    protected $strategyHelper;

    /**
     * @var ContactImportStrategyHelper
     */
    protected $contactStrategyHelper;

    /**
     * @var ContextInterface
     */
    protected $context;

    /**
     * @param ImportStrategyHelper $strategyHelper
     * @param ContactImportStrategyHelper $contactStrategyHelper
     */
    public function __construct(
        ImportStrategyHelper $strategyHelper,
        ContactImportStrategyHelper $contactStrategyHelper
    ) {
        $this->strategyHelper = $strategyHelper;
        $this->contactStrategyHelper = $contactStrategyHelper;
    }

    /**
     * {@inheritDoc}
     */
    public function process($entity)
    {
        if (!$this->context) {
            throw new LogicException('Strategy must have import/export context');
        }

        if (!$entity instanceof Contact) {
            throw new InvalidArgumentException('Imported entity must be instance of Contact');
        }

        // try to find existing contact by ID, if it exists - replace all data with data from imported contact
        $entity = $this->findAndReplaceContact($entity);

        // update all related entities
        $this
            ->updateSource($entity)
            ->updateMethod($entity)
            ->updateOwner($entity)
            ->updateAssignedTo($entity)
            ->updateAddresses($entity)
            ->updateGroups($entity)
            ->updateAccounts($entity);

        // update owner for addresses, emails and phones
        $this->updateRelatedEntitiesOwner($entity);

        // validate and update context - increment counter or add validation error
        $entity = $this->validateAndUpdateContext($entity);

        return $entity;
    }

    /**
     * {@inheritDoc}
     */
    public function setImportExportContext(ContextInterface $context)
    {
        $this->context = $context;
    }

    /**
     * @param Contact $contact
     * @return null|Contact
     */
    protected function validateAndUpdateContext(Contact $contact)
    {
        // validate contact
        $validationErrors = $this->strategyHelper->validateEntity($contact);
        if ($validationErrors) {
            $this->context->incrementErrorEntriesCount();
            $this->strategyHelper->addValidationErrors($validationErrors, $this->context);
            return null;
        }

        // increment context counter
        if ($contact->getId()) {
            $this->context->incrementReplaceCount();
        } else {
            $this->context->incrementAddCount();
        }

        return $contact;
    }

    /**
     * @param Contact $contact
     * @return AddOrReplaceStrategy
     */
    protected function updateRelatedEntitiesOwner(Contact $contact)
    {
        foreach ($contact->getAddresses() as $address) {
            $address->setOwner($contact);
        }

        foreach ($contact->getEmails() as $email) {
            $email->setOwner($contact);
        }

        foreach ($contact->getPhones() as $phone) {
            $phone->setOwner($contact);
        }

        return $this;
    }

    /**
     * @param Contact $contact
     * @return AddOrReplaceStrategy
     */
    protected function updateAccounts(Contact $contact)
    {
        foreach ($contact->getAccounts() as $account) {
            $contact->removeAccount($account);
            $existingAccount = $this->contactStrategyHelper->getAccountOrNull($account);
            if ($existingAccount) {
                $contact->addAccount($existingAccount);
            }
        }

        return $this;
    }

    /**
     * @param Contact $contact
     * @return AddOrReplaceStrategy
     */
    protected function updateGroups(Contact $contact)
    {
        foreach ($contact->getGroups() as $contactGroup) {
            $contact->removeGroup($contactGroup);
            $existingGroup = $this->contactStrategyHelper->getGroupOrNull($contactGroup);
            if ($existingGroup) {
                $contact->addGroup($existingGroup);
            }
        }

        return $this;
    }

    /**
     * @param Contact $contact
     * @return AddOrReplaceStrategy
     */
    protected function updateAddresses(Contact $contact)
    {
        foreach ($contact->getAddresses() as $contactAddress) {
            // update country
            $country = $contactAddress->getCountry();
            if ($country) {
                $existingCountry = $this->contactStrategyHelper->getCountryOrNull($country);
                $contactAddress->setCountry($existingCountry);
            } else {
                $contactAddress->setCountry(null);
            }

            // update region
            $region = $contactAddress->getRegion();
            if ($region) {
                $existingRegion = $this->contactStrategyHelper->getRegionOrNull($region);
                $contactAddress->setRegion($existingRegion);
            } else {
                $contactAddress->setRegion(null);
            }

            // update address types
            foreach ($contactAddress->getTypes() as $addressType) {
                $contactAddress->removeType($addressType);
                $existingAddressType = $this->contactStrategyHelper->getAddressTypeOrNull($addressType);
                if ($existingAddressType) {
                    $contactAddress->addType($existingAddressType);
                }
            }
        }

        return $this;
    }

    /**
     * @param Contact $contact
     * @return AddOrReplaceStrategy
     */
    protected function updateSource(Contact $contact)
    {
        $source = $contact->getSource();
        if ($source) {
            $existingSource = $this->contactStrategyHelper->getSourceOrNull($source);
            $contact->setSource($existingSource);
        } else {
            $contact->setSource(null);
        }

        return $this;
    }

    /**
     * @param Contact $contact
     * @return AddOrReplaceStrategy
     */
    protected function updateMethod(Contact $contact)
    {
        $method = $contact->getMethod();
        if ($method) {
            $existingMethod = $this->contactStrategyHelper->getMethodOrNull($method);
            $contact->setMethod($existingMethod);
        } else {
            $contact->setMethod(null);
        }

        return $this;
    }

    /**
     * @param Contact $contact
     * @return AddOrReplaceStrategy
     */
    protected function updateOwner(Contact $contact)
    {
        $owner = $contact->getOwner();
        if ($owner) {
            $existingOwner = $this->contactStrategyHelper->getUserOrNull($owner);
            $contact->setOwner($existingOwner);
        } else {
            $contact->setOwner(null);
        }

        return $this;
    }

    /**
     * @param Contact $contact
     * @return AddOrReplaceStrategy
     */
    protected function updateAssignedTo(Contact $contact)
    {
        $assignedTo = $contact->getAssignedTo();
        if ($assignedTo) {
            $existingAssignedTo = $this->contactStrategyHelper->getUserOrNull($assignedTo);
            $contact->setAssignedTo($existingAssignedTo);
        } else {
            $contact->setAssignedTo(null);
        }

        return $this;
    }

    /**
     * @param Contact $contact
     * @return Contact
     */
    protected function findAndReplaceContact(Contact $contact)
    {
        $existingContact = $this->contactStrategyHelper->getContactOrNull($contact);
        if ($existingContact) {
            $this->removeRelatedCreatedEntities($existingContact);
            $this->strategyHelper->importEntity($existingContact, $contact);
            $this->updateCreatedAndUpdatedFields($existingContact);
            $contact = $existingContact;
        } else {
            $contact->setId(null);
        }

        return $contact;
    }

    /**
     * @param Contact $contact
     * @return AddOrReplaceStrategy
     */
    protected function updateCreatedAndUpdatedFields(Contact $contact)
    {
        $currentDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $currentUser = $this->contactStrategyHelper->getSecurityContextUserOrNull();

        $contact
            ->setCreatedAt($currentDate)
            ->setUpdatedAt($currentDate)
            ->setCreatedBy($currentUser)
            ->setUpdatedBy($currentUser);

        return $this;
    }

    /**
     * @param Contact $contact
     * @return AddOrReplaceStrategy
     */
    protected function removeRelatedCreatedEntities(Contact $contact)
    {
        $contact
            ->resetAddresses(array())
            ->resetEmails(array())
            ->resetPhones(array());

        return $this;
    }
}
