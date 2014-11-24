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
            $replaced = preg_replace('/<apiKey.*?>(.*)<\/apiKey>/i', '', $exception);

            if ($exception !== $replaced) {
                $exceptionCollection[] = ['remove' => $exception, 'insert' => $replaced];
            }
        }

        foreach ($exceptionCollection as $forRemove) {
            $jobResult->removeFailureException($forRemove['remove']);
            $jobResult->addFailureException($forRemove['insert']);
        }
    }
}
