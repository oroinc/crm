<?php

namespace Oro\Bundle\SalesBundle\Provider\Customer;

/**
 * Delegates receiving of an icon for a specific customer to child providers.
 */
class ChainCustomerIconProvider implements CustomerIconProviderInterface
{
    /** @var iterable|CustomerIconProviderInterface[] */
    private $providers;

    /**
     * @param iterable|CustomerIconProviderInterface[] $providers
     */
    public function __construct(iterable $providers)
    {
        $this->providers = $providers;
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon($entity)
    {
        foreach ($this->providers as $provider) {
            $icon = $provider->getIcon($entity);
            if (null !== $icon) {
                return $icon;
            }
        }

        return null;
    }
}
