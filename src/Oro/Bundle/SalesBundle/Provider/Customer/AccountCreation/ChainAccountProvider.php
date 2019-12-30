<?php

namespace Oro\Bundle\SalesBundle\Provider\Customer\AccountCreation;

use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * Delegates receiving of an account for a specific customer to child providers.
 */
class ChainAccountProvider implements AccountProviderInterface
{
    /** @var iterable|AccountProviderInterface[] */
    private $providers;

    /**
     * @param iterable|AccountProviderInterface[] $providers
     */
    public function __construct(iterable $providers)
    {
        $this->providers = $providers;
    }

    /**
     * {@inheritdoc}
     */
    public function getAccount($targetCustomer)
    {
        foreach ($this->providers as $provider) {
            $account = $provider->getAccount($targetCustomer);
            if (null !== $account) {
                return $account;
            }
        }

        throw new RuntimeException(
            'Unable to provide an account. There are no providers registered in the system.'
        );
    }
}
