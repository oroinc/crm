<?php

namespace Oro\Bundle\SalesBundle\Provider;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOptionInterface;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

/**
 * Simple wrapper over ConfigManager to receive probabilities
 */
class ProbabilityProvider
{
    /** @var ConfigManager */
    protected $configManager;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * @param EnumOptionInterface $status
     *
     * @return bool
     */
    public function has(EnumOptionInterface $status)
    {
        $probabilities = $this->getAll();

        return array_key_exists($status->getId(), $probabilities);
    }

    /**
     * @param EnumOptionInterface $status
     *
     * @return float|null
     */
    public function get(EnumOptionInterface $status)
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
        return $this->configManager->get(Opportunity::PROBABILITIES_CONFIG_KEY);
    }
}
