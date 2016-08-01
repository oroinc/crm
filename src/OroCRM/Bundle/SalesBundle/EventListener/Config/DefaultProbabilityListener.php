<?php

namespace OroCRM\Bundle\SalesBundle\EventListener\Config;

use Oro\Bundle\ConfigBundle\Event\ConfigGetEvent;

class DefaultProbabilityListener
{
    /**
     * Merge non-configured default probabilities with those defined in parent
     *
     * @param ConfigGetEvent $event
     */
    public function loadConfig(ConfigGetEvent $event)
    {
        $name = $event->getKey();
        $configManager = $event->getConfigManager();

        $value = $configManager->getSettingsDefaults($name, $event->isFull());
        $value = $configManager->getMergedWithParentValue($value, $name, $event->isFull());

        $event->setValue($value);
    }
}
