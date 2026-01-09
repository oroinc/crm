<?php

namespace Oro\Bundle\SalesBundle\Provider\Customer\AccountCreation;

use Oro\Bundle\AccountBundle\Entity\Account;

/**
 * Defines the contract for account creation strategies, allowing different approaches to create or select accounts
 * during customer conversion.
 */
interface AccountProviderInterface
{
    /**
     * Creates new Account for customer association based on the $targetCustomer entity
     *
     * @param object $targetCustomer
     *
     * @return Account|null
     */
    public function getAccount($targetCustomer);
}
