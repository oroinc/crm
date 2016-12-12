<?php

namespace Oro\Bundle\SalesBundle\Provider\Customer;

class ChainCustomerIconProvider implements CustomerIconProviderInterface
{
    /** @var CustomerIconProviderInterface[] */
    protected $providers = [];

    /**
     * @param CustomerIconProviderInterface $provider
     */
    public function addProvider(CustomerIconProviderInterface $provider)
    {
        if (!in_array($provider, $this->providers, true)) {
            $this->providers[] = $provider;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon($entity)
    {
        foreach ($this->providers as $provider) {
            if ($image = $provider->getIcon($entity)) {
                return $image;
            }
        }

        return null;
    }
}
