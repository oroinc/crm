<?php

namespace Oro\Bundle\SalesBundle\Provider\Customers;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

class SalesItemsProvider
{
    /**
     * @var SalesItemsProviderInterface[]
     */
    protected $providers = [];

    /** @var ConfigProvider */
    protected $extendProvider;

    /** @var ConfigProvider */
    protected $salesProvider;

    /**
     * @param ConfigManager     $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->extendProvider   = $configManager->getProvider('extend');
        $this->salesProvider   = $configManager->getProvider('sales');
        
    }

    public function supportCustomer($customerClass)
    {
        foreach ($this->providers as $provider) {
            if ($provider->supportCustomer($customerClass)) {
                return true;
            }
        }

        return false;
    }

    public function getCustomerSalesItems($customerClass, $salesItemClass)
    {
        return;
    }

    public function supportCustomerSalesItems($customerClass, $salesItemClass)
    {
        foreach ($this->providers as $provider) {
            if ($provider->supportCustomerSalesItems($customerClass, $salesItemClass)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Registers the given provider in the chain
     *
     * @param SalesItemsProviderInterface $provider
     */
    public function addProvider(SalesItemsProviderInterface $provider, $priority = null)
    {
        $this->providers[] = $provider;
    }
}
