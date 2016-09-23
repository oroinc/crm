<?php

namespace Oro\Bundle\MagentoBundle\EventListener;

use Oro\Bundle\IntegrationBundle\Event\SyncEvent;
use Oro\Bundle\MagentoBundle\Utils\ValidationUtils;

class IntegrationSyncAfterEventListener
{
    /**
     * Replace sensitive data in sync job results
     *
     * @param SyncEvent $event
     */
    public function process(SyncEvent $event)
    {
        if (strpos($event->getJobName(), 'mage_') === false) {
            return;
        }

        $jobResult  = $event->getJobResult();
        $exceptions = array_map(
            function ($exception) {
                $exception = ValidationUtils::sanitizeSecureInfo($exception);
                return $exception;
            },
            $jobResult->getFailureExceptions()
        );

        $jobResult->setFailureExceptions($exceptions);
    }
}
