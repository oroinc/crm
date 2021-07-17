<?php

namespace Oro\Bundle\SalesBundle\Provider\Customer\AccountCreation;

use Oro\Bundle\SalesBundle\Entity\B2bCustomer;

class B2bAccountProvider implements AccountProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getAccount($targetCustomer)
    {
        if ($targetCustomer instanceof B2bCustomer && $targetCustomer->getAccount()) {
            return $targetCustomer->getAccount();
        }

        return null;
    }
}
