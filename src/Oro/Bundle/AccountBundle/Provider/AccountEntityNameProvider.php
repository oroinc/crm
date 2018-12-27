<?php

namespace Oro\Bundle\AccountBundle\Provider;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;

/**
 * Account entity name should be equal only to the value of its name field
 */
class AccountEntityNameProvider implements EntityNameProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function getName($format, $locale, $entity)
    {
        if ($format === EntityNameProviderInterface::FULL && is_a($entity, Account::class, true)) {
            return (string)$entity->getName();
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getNameDQL($format, $locale, $className, $alias)
    {
        return false;
    }
}
