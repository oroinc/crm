<?php

namespace Oro\Bundle\SalesBundle\EventListener\Config;

use Oro\Bundle\ConfigBundle\Event\ConfigGetEvent;

/**
 * Merges non-configured default probabilities with those defined in parent config.
 */
class DefaultProbabilityListener
{
    public function loadConfig(ConfigGetEvent $event): void
    {
        $event->setValue(
            $event->getConfigManager()->getMergedWithParentValue(
                $event->getValue(),
                $event->getKey(),
                $event->isFull()
            )
        );
    }
}
