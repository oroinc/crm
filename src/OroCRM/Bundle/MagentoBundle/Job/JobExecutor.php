<?php

namespace OroCRM\Bundle\MagentoBundle\Job;

use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\IntegrationBundle\ImportExport\Job\Executor;
use OroCRM\Bundle\MagentoBundle\Provider\CartConnector;
use OroCRM\Bundle\MagentoBundle\Provider\CustomerConnector;
use OroCRM\Bundle\MagentoBundle\Provider\InitialSyncProcessor;
use OroCRM\Bundle\MagentoBundle\Provider\OrderConnector;

class JobExecutor extends Executor
{
    /**
     * {@inheritdoc}
     */
    public function executeJob($jobType, $jobName, array $configuration = [])
    {
        if (empty($configuration[ProcessorRegistry::TYPE_IMPORT][InitialSyncProcessor::INITIAL_SYNCED_TO])) {
            return parent::executeJob($jobType, $jobName, $configuration);
        }

        if (empty($configuration[ProcessorRegistry::TYPE_IMPORT][InitialSyncProcessor::START_SYNC_DATE])) {
            return parent::executeJob($jobType, $jobName, $configuration);
        }

        /** @var \DateTime $startSyncDate */
        $startSyncDate = $configuration[ProcessorRegistry::TYPE_IMPORT][InitialSyncProcessor::START_SYNC_DATE];

        /** @var \DateTime $initialSyncedTo */
        $initialSyncedTo = $configuration[ProcessorRegistry::TYPE_IMPORT][InitialSyncProcessor::INITIAL_SYNCED_TO];

        $jobResults = [];

        while ($startSyncDate < $initialSyncedTo) {
            $initialSyncedTo->modify('-1 day');
            $configuration[ProcessorRegistry::TYPE_IMPORT][InitialSyncProcessor::INITIAL_SYNCED_TO] = $initialSyncedTo;

            $jobResults[] = parent::executeJob($jobType, $jobName, $configuration);
        }

        return reset($jobResults);
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable($jobType, $jobName)
    {
        $supportedJobs = [
            ProcessorRegistry::TYPE_IMPORT => [
                OrderConnector::IMPORT_JOB_NAME,
                CartConnector::IMPORT_JOB_NAME,
                CustomerConnector::IMPORT_JOB_NAME
            ]
        ];

        if (empty($supportedJobs[$jobType])) {
            return false;
        }

        return in_array($jobName, $supportedJobs[$jobType], true);
    }
}
