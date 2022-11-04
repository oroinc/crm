<?php

namespace Oro\Bundle\ContactBundle\EventListener;

use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Oro\Bundle\BatchBundle\Event\JobExecutionEvent;
use Oro\Bundle\BatchBundle\Job\BatchStatus;
use Oro\Bundle\ContactBundle\Async\Topic\ActualizeContactEmailAssociationsTopic;
use Oro\Bundle\ImportExportBundle\Configuration\ImportExportConfigurationProviderInterface;
use Oro\Bundle\PlatformBundle\Manager\OptionalListenerManager;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;

/**
 * Disable Email EntityListener to prevent creation of EmailAddress entities per each contact during import
 * EmailAddress records will be later actualized by ContactPostImportProcessor
 */
class ImportEventListener
{
    private OptionalListenerManager $optionalListenerManager;

    private ImportExportConfigurationProviderInterface $contactImportExportConfigurationProvider;

    private MessageProducerInterface $messageProducer;

    public function __construct(
        OptionalListenerManager $optionalListenerManager,
        ImportExportConfigurationProviderInterface $contactImportExportConfigurationProvider,
        MessageProducerInterface $messageProducer
    ) {
        $this->optionalListenerManager = $optionalListenerManager;
        $this->contactImportExportConfigurationProvider = $contactImportExportConfigurationProvider;
        $this->messageProducer = $messageProducer;
    }

    public function onBeforeJobExecution(JobExecutionEvent $jobExecutionEvent): void
    {
        if (!$this->isSupportedJob($jobExecutionEvent->getJobExecution())) {
            return;
        }

        $this->optionalListenerManager->disableListener('oro_email.listener.entity_listener');
    }

    public function onAfterJobExecution(JobExecutionEvent $jobExecutionEvent): void
    {
        $jobExecution = $jobExecutionEvent->getJobExecution();
        if (!$this->isSupportedJob($jobExecution)
            && $jobExecution->getStatus()->getValue() !== BatchStatus::COMPLETED) {
            return;
        }

        $this->optionalListenerManager->enableListener('oro_email.listener.entity_listener');

        $this->messageProducer->send(ActualizeContactEmailAssociationsTopic::getName(), []);
    }

    private function isSupportedJob(JobExecution $jobExecution): bool
    {
        $config = $this->contactImportExportConfigurationProvider->get();
        $executionContext = $jobExecution->getExecutionContext();

        return $config->getEntityClass() === $executionContext->get('entityName')
            && $config->getImportProcessorAlias() === $executionContext->get('processorAlias');
    }
}
