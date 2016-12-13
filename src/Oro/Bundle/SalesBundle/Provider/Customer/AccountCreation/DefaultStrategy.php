<?php

namespace Oro\Bundle\SalesBundle\Provider\Customer\AccountCreation;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;

class DefaultStrategy implements AccountProviderInterface
{
    /** @var EntityNameResolver */
    protected $nameResolver;

    /**
     * @param EntityNameResolver $resolver
     */
    public function __construct(EntityNameResolver $resolver)
    {
        $this->nameResolver = $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getAccount($targetCustomer)
    {
        return (new Account())
            ->setName($this->nameResolver->getName($targetCustomer));
    }
}
