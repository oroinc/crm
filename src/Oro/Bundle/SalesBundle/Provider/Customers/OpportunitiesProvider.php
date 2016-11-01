<?php

namespace Oro\Bundle\SalesBundle\Provider\Customers;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

class OpportunitiesProvider implements SalesItemsProviderInterface
{
    /** @var ConfigProvider */
    protected $extendProvider;

    /** @var ConfigProvider */
    protected $salesProvider;


    /** @var ConfigProvider */
    protected $salesOpportunityProvider;

    /**
     * @param ConfigManager     $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->extendProvider   = $configManager->getProvider('extend');
        $this->salesOpportunityProvider    = $configManager->getProvider('sales_opportunity');
        $this->salesProvider = $configManager->getProvider('sales');
    }

    public function supportCustomer($customerClass)
    {
        return isset($this->salesProvider->getConfig(Opportunity::class)->get('customers', [])[$customerClass]);
    }

    public function supportCustomerSalesItems($customerClass, $salesItemClass)
    {//@todo move to parent abstract class or helper
        return isset($this->salesProvider->getConfig($salesItemClass)->get('customers', [])[$customerClass]);
    }
}
