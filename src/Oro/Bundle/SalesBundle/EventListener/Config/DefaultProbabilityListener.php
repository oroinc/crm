<?php

namespace Oro\Bundle\SalesBundle\EventListener\Config;

use Oro\Bundle\ConfigBundle\Event\ConfigGetEvent;

/**
 * Listens to ConfigGetEvent to merge probabilities
 */
class DefaultProbabilityListener
{
    /**
     * Merge non-configured default probabilities with those defined in parent
     */
    public function loadConfig(ConfigGetEvent $event)
    {
        $name = $event->getKey();
        $configManager = $event->getConfigManager();

        $value = $configManager->getMergedWithParentValue($event->getValue(), $name, $event->isFull());

        $event->setValue($value);
    }
}
