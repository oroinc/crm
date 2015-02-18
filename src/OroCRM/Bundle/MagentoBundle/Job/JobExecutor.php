<?php

namespace OroCRM\Bundle\MagentoBundle\Job;

use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\IntegrationBundle\ImportExport\Job\Executor;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;
use OroCRM\Bundle\MagentoBundle\Provider\InitialSyncProcessor;

class JobExecutor extends Executor
{
    /** @var ConnectorInterface[] */
    protected $connectors = [];

    /**
     * @param ConnectorInterface $connector
     */
    public function addConnector(ConnectorInterface $connector)
    {
        $this->connectors[] = $connector;
    }

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
            $jobResults[] = parent::executeJob($jobType, $jobName, $configuration);

            $initialSyncedTo->modify('-1 day');
            $configuration[ProcessorRegistry::TYPE_IMPORT][InitialSyncProcessor::INITIAL_SYNCED_TO] = $initialSyncedTo;
        }

        return reset($jobResults);
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable($jobType, $jobName)
    {
        $jobs = [];
        foreach ($this->connectors as $connector) {
            $jobs[$connector->getType()][$connector->getImportJobName()] = true;
        }

//        return in_array($jobName, ['mage_order_import'], true);

        return !empty($jobs[$jobType][$jobName]);
    }
}
