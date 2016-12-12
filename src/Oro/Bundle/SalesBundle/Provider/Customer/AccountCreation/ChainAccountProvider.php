<?php

namespace Oro\Bundle\SalesBundle\Provider\Customer\AccountCreation;

use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class ChainAccountProvider implements AccountProviderInterface
{
    /** @var AccountProviderInterface[] */
    protected $providers = [];

    /**
     * @param AccountProviderInterface $provider
     */
    public function addProvider(AccountProviderInterface $provider)
    {
        if (!in_array($provider, $this->providers, true)) {
            $this->providers[] = $provider;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function provideAccount($entity)
    {
        foreach ($this->providers as $provider) {
            if ($account = $provider->provideAccount($entity)) {
                return $account;
            }
        }

        throw new ServiceNotFoundException(
            'Unable to provide an account. There are no providers registered in the system.'
        );
    }
}
