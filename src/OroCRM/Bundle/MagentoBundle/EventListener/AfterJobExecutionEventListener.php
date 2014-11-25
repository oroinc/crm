<?php

namespace OroCRM\Bundle\MagentoBundle\EventListener;

use Oro\Bundle\IntegrationBundle\Event\AfterJobExecutionEvent;

class AfterJobExecutionEventListener
{
    /**
     * @param AfterJobExecutionEvent $event
     */
    public function process(AfterJobExecutionEvent $event)
    {
        $jobResult           = $event->getJobResult();
        $exceptions          = $jobResult->getFailureExceptions();
        $exceptionCollection = [];

        foreach ($exceptions as $exception) {
            $exceptionCollection[] = preg_replace('/<apiKey.*?>(.*)<\/apiKey>/i', '', $exception);
        }

        $jobResult->setFailureException($exceptionCollection);
    }
}
