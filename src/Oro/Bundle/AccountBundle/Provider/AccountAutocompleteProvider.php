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
        return $this->getContactInfo(
            $entity,
            static function($contact, &$data) {
                foreach ($contact->getEmails() as $contactEmail) {
                    $data[] = $contactEmail->getEmail();
                }
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPhones($entity)
    {
        return $this->getContactInfo(
            $entity,
            static function($contact, &$data) {
                foreach ($contact->getPhones() as $contactPhone) {
                    $data[] = $contactPhone->getPhone();
                }
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getNames($entity)
    {
        return $this->getContactInfo(
            $entity,
            static function($contact, &$data) {
                $data[] = $contact->getNamePrefix() .
                $contact->getFirstName() .
                $contact->getMiddleName() .
                $contact->getLastName() .
                $contact->getNameSuffix();
            }
        );
    }

    protected function getContactInfo($entity, $callback)
    {
        if (!$this->isSupportEntity($entity)) {
            return [];
        }

        foreach ($entity->getContacts() as $contact) {
            $callback($contact, &$data);
        }
        
        return $data ?? [];
    }
}
