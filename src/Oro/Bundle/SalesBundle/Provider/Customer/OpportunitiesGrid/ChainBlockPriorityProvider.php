<?php

namespace Oro\Bundle\SalesBundle\Provider\Customer\OpportunitiesGrid;

class ChainBlockPriorityProvider implements BlockPriorityProviderInterface
{
    /** @var BlockPriorityProviderInterface[] */
    protected $providers = [];

    /**
     * @param BlockPriorityProviderInterface $provider
     */
    public function addProvider(BlockPriorityProviderInterface $provider)
    {
        if (!in_array($provider, $this->providers, true)) {
            $this->providers[] = $provider;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority($targetClass)
    {
        foreach ($this->providers as $provider) {
            if ($priority = $provider->getPriority($targetClass)) {
                return $priority;
            }
        }

        return null;
    }
}
