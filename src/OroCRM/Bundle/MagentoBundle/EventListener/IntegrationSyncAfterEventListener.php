<?php

namespace OroCRM\Bundle\MagentoBundle\EventListener;

use Oro\Bundle\IntegrationBundle\Event\SyncEvent;

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
                if (is_string($exception)) {
                    return preg_replace('#(<apiKey.*?>)(.*)(</apiKey>)#i', '$1***$3', $exception);
                }

                return $exception;
            },
            $jobResult->getFailureExceptions()
        );

        $jobResult->setFailureExceptions($exceptions);
    }
}
