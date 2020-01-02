<?php

namespace Oro\Bundle\SalesBundle\Provider\Customer\AccountAutocomplete;

/**
 * Delegates receiving of entity autocomplete data to child providers.
 */
class ChainAccountAutocompleteProvider
{
    /** @var iterable|AccountAutocompleteProviderInterface[] */
    private $providers;

    /**
     * @param iterable|AccountAutocompleteProviderInterface[] $providers
     */
    public function __construct(iterable $providers)
    {
        $this->providers = $providers;
    }

    /**
     * Collect additional data about entity to show in autocomplete results.
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
