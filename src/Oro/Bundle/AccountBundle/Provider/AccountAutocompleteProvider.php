<?php

namespace Oro\Bundle\AccountBundle\Provider;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\SalesBundle\Provider\Customer\AccountAutocomplete\AccountAutocompleteProviderInterface;

class AccountAutocompleteProvider implements AccountAutocompleteProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function isSupportEntity($entity)
    {
        return $entity instanceof Account;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmails($entity)
    {
        $data = [];

        if (!$entity instanceof Account) {
            return $data;
        }

        $contacts = $entity->getContacts();
        foreach ($contacts as $contact) {
            $contactEmails = $contact->getEmails();
            foreach ($contactEmails as $contactEmail) {
                $email = $contactEmail->getEmail();
                $data[] = $email;
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getPhones($entity)
    {
        $data = [];

        if (!$entity instanceof Account) {
            return $data;
        }

        $contacts = $entity->getContacts();
        foreach ($contacts as $contact) {
            $contactPhones = $contact->getPhones();
            foreach ($contactPhones as $contactPhone) {
                $phone = $contactPhone->getPhone();
                $data[] = $phone;
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getNames($entity)
    {
        $data = [];

        if (!$entity instanceof Account) {
            return $data;
        }

        $contacts = $entity->getContacts();
        foreach ($contacts as $contact) {
            $contactName = $contact->getNamePrefix() .
                $contact->getFirstName() .
                $contact->getMiddleName() .
                $contact->getLastName() .
                $contact->getNameSuffix();

            $data[] = $contactName;
        }

        return $data;
    }
}
