<?php

namespace Oro\Bundle\MagentoBundle\Provider\Customer;

use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Provider\Customer\AccountAutocomplete\AccountAutocompleteProviderInterface;

class AccountAutocompleteProvider implements AccountAutocompleteProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function isSupportEntity($entity)
    {
        return $entity instanceof Customer;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmails($entity)
    {
        if (!$entity instanceof Customer) {
            return [];
        }

        $data = [];
        $data[] = $entity->getEmail();

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getPhones($entity)
    {
        if (!$entity instanceof Customer) {
            return [];
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getNames($entity)
    {
        if (!$entity instanceof Customer) {
            return [];
        }

        $data = [];

        $contactName = $entity->getNamePrefix() .
            $entity->getFirstName().
            $entity->getMiddleName().
            $entity->getLastName().
            $entity->getNameSuffix();
        $data[] = $contactName;

        return $data;
    }
}
