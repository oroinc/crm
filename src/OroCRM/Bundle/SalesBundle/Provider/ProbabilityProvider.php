<?php

namespace OroCRM\Bundle\SalesBundle\Provider;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

/**
 * Simple wrapper over ConfigManager to receive probabilities
 */
class ProbabilityProvider
{
    const PROBABILITIES_CONFIG_KEY = 'oro_crm_sales.default_opportunity_probabilities';

    /** @var ConfigManager */
    protected $configManager;

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * @param AbstractEnumValue $status
     *
     * @return bool
     */
    public function has(AbstractEnumValue $status)
    {
        $probabilities = $this->getAll();

        return isset($probabilities[$status->getId()]);
    }

    /**
     * @param AbstractEnumValue $status
     *
     * @return float|null
     */
    public function get(AbstractEnumValue $status)
    {
        if ($this->has($status)) {
            return $this->getAll()[$status->getId()];
        }
    }

    /**
     * @return array Return map of status id to probability
     */
    public function getAll()
    {
        return $this->configManager->get(self::PROBABILITIES_CONFIG_KEY);
    }
}
