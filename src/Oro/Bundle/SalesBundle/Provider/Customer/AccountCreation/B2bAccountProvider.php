<?php

namespace Oro\Bundle\SalesBundle\Provider\Customer\AccountCreation;

use Oro\Bundle\SalesBundle\Entity\B2bCustomer;

/**
 * Provides account creation strategy for B2B customers, creating accounts from customer data.
 */
class B2bAccountProvider implements AccountProviderInterface
{
    #[\Override]
    public function getAccount($targetCustomer)
    {
        if ($targetCustomer instanceof B2bCustomer && $targetCustomer->getAccount()) {
            return $targetCustomer->getAccount();
        }

        return null;
    }
}
