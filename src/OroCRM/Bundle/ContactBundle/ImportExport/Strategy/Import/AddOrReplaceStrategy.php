<?php

namespace OroCRM\Bundle\ContactBundle\ImportExport\Strategy\Import;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ImportExportBundle\Exception\InvalidArgumentException;

class AddOrReplaceStrategy extends AbstractContactImportStrategy
{
    /**
     * {@inheritDoc}
     */
    public function process($entity)
    {
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

        return $entity;
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
            $existingAccount= $this->getAccountOrNull($account);
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
            $existingGroup = $this->getGroupOrNull($contactGroup);
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
                $existingCountry = $this->getCountryOrNull($country);
                $contactAddress->setCountry($existingCountry);
            } else {
                $contactAddress->setCountry(null);
            }

            // update region
            $region = $contactAddress->getRegion();
            if ($region) {
                $existingRegion = $this->getRegionOrNull($region);
                $contactAddress->setRegion($existingRegion);
            } else {
                $contactAddress->setRegion(null);
            }

            // update address types
            foreach ($contactAddress->getTypes() as $addressType) {
                $contactAddress->removeType($addressType);
                $existingAddressType = $this->getAddressTypeOrNull($addressType);
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
            $existingSource = $this->getSourceOrNull($source);
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
            $existingMethod = $this->getMethodOrNull($method);
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
        if ($contact->getOwner()) {
            $existingOwner = $this->getUserOrNull($contact->getOwner());
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
        if ($contact->getAssignedTo()) {
            $existingAssignedTo = $this->getUserOrNull($contact->getAssignedTo());
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
        $contactId = $contact->getId();
        if ($contactId) {
            /** @var Contact $existingContact */
            $existingContact = $this->getEntityRepository($this->entityClass)->find($contactId);
            if ($existingContact) {
                $this->importEntity(
                    $existingContact,
                    $contact,
                    array('tags', 'reportsTo', 'nameFormat', 'createdAt', 'createdBy')
                );
                $contact = $existingContact;
                $contact->setUpdatedAt(new \DateTime('now', new \DateTimeZone('UTC')));
                $contact->setUpdatedBy($this->getCurrentUser());
            } else {
                $contact->setId(null);
            }
        }

        return $contact;
    }
}
