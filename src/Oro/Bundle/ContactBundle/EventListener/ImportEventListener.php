<?php

namespace Oro\Bundle\ContactBundle\EventListener;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Event\JobExecutionEvent;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\PlatformBundle\Manager\OptionalListenerManager;

/**
 * Disable Email EntityListener to prevent creation of EmailAddress entities per each contact during import
 * EmailAddress records will be later actualized by ContactPostImportProcessor
 */
class ImportEventListener
{
    /**
     * @var OptionalListenerManager
     */
    private $listenerManager;

    public function __construct(OptionalListenerManager $listenerManager)
    {
        $this->listenerManager = $listenerManager;
    }

    public function onBeforeJobExecution(JobExecutionEvent $jobExecutionEvent)
    {
        if ($this->isSupportedJob($jobExecutionEvent->getJobExecution())) {
            return;
        }

        $this->listenerManager->disableListener('oro_email.listener.entity_listener');
    }

    public function onAfterJobExecution(JobExecutionEvent $jobExecutionEvent)
    {
        if ($this->isSupportedJob($jobExecutionEvent->getJobExecution())) {
            return;
        }

        $this->listenerManager->enableListener('oro_email.listener.entity_listener');
    }

    /**
     * @param JobExecution $jobExecution
     * @return bool
     */
    protected function isSupportedJob($jobExecution): bool
    {
        return $jobExecution->getExecutionContext()->get('entityName') !== Contact::class;
    }
}
