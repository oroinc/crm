<?php

namespace Oro\Bundle\SalesBundle\Tests\Behat\Context;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;

/**
 * This context save behat execution time, all detailed steps can be found at
 * - "Manage Opportunity Feature"
 * - "Manage Salesfunnel Feature"
 * - "Manage Lead Feature"
 */
class SalesFeatureToggleContext extends OroFeatureContext
{
    private ConfigManager $configManager;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * @When /^(?:|I )enable Opportunity feature$/
     */
    public function enableOpportunityFeature()
    {
        $this->setFeatureState(1, 'oro_sales', 'opportunity_feature_enabled');
    }

    /**
     * @When /^(?:|I )disable Opportunity feature$/
     */
    public function disableOpportunityFeature()
    {
        $this->setFeatureState(0, 'oro_sales', 'opportunity_feature_enabled');
    }

    /**
     * @When /^(?:|I )enable Lead feature$/
     */
    public function enableLeadFeature()
    {
        $this->setFeatureState(1, 'oro_sales', 'lead_feature_enabled');
    }

    /**
     * @When /^(?:|I )disable Lead feature$/
     */
    public function disableLeadFeature()
    {
        $this->setFeatureState(0, 'oro_sales', 'lead_feature_enabled');
    }

    /**
     * @When /^(?:|I )enable SalesFunnel feature$/
     */
    public function enableSalesFunnelFeature()
    {
        $this->setFeatureState(1, 'oro_sales', 'salesfunnel_feature_enabled');
    }

    /**
     * @When /^(?:|I )disable SalesFunnel feature$/
     */
    public function disableSalesFunnelFeature()
    {
        $this->setFeatureState(0, 'oro_sales', 'salesfunnel_feature_enabled');
    }

    /**
     * @param mixed $state
     * @param string $section
     * @param string $name
     */
    protected function setFeatureState($state, $section, $name)
    {
        $this->configManager->set(sprintf('%s.%s', $section, $name), $state ? 1 : 0);
        $this->configManager->flush();
    }
}
