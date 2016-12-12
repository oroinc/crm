<?php

namespace Oro\Bundle\SalesBundle\Provider\Customer\AccountCreation;

use Oro\Bundle\AccountBundle\Entity\Account;

interface AccountProviderInterface
{
    /**
     * Creates Account from customer target $entity
     *
     * @param $entity
     *
     * @return Account|null
     */
    public function provideAccount($entity);
}
