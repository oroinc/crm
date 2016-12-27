<?php

namespace Oro\Bundle\SalesBundle\Provider\Customer\AccountAutocomplete;

class ChainAccountAutocompleteProvider
{
    /** @var AccountAutocompleteProviderInterface[] */
    protected $providers = [];

    /**
     * @param AccountAutocompleteProviderInterface $provider
     */
    public function addProvider(AccountAutocompleteProviderInterface $provider)
    {
        if (!in_array($provider, $this->providers, true)) {
            $this->providers[] = $provider;
        }
    }

    /**
     * Collect additional data about entity to show in autocomplete results
     *
     * @param $entity
     *
     * @return array
     */
    public function getData($entity)
    {
        $data = [];

        foreach ($this->providers as $provider) {
            if ($provider->isSupportEntity($entity)) {
                $data['emails'] = $provider->getEmails($entity);
                $data['phones'] = $provider->getPhones($entity);
                $data['names'] = $provider->getNames($entity);
            }
        }

        return $data;
    }
}
