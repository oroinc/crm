<?php

namespace Oro\Bundle\SalesBundle\Provider\Opportunity;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class DisplaySettingsConfigProvider
{
    const CONFIG_DISPLAY_RELEVANT_DATA = 'oro_sales.display_relevant_opportunities';

    /** @var ConfigManager */
    protected $configManager;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * @return bool
     */
    public function isFeatureEnabled()
    {
        return (bool) $this->configManager->get(self::CONFIG_DISPLAY_RELEVANT_DATA);
    }
}
