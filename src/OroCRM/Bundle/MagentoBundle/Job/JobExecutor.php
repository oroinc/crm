<?php

namespace OroCRM\Bundle\MagentoBundle\Job;

use Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Akeneo\Bundle\BatchBundle\Job\DoctrineJobRepository;

use Symfony\Bridge\Doctrine\ManagerRegistry;

use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\IntegrationBundle\ImportExport\Job\Executor;
use OroCRM\Bundle\MagentoBundle\Provider\CartConnector;
use OroCRM\Bundle\MagentoBundle\Provider\CustomerConnector;
use OroCRM\Bundle\MagentoBundle\Provider\InitialSyncProcessor;
use OroCRM\Bundle\MagentoBundle\Provider\OrderConnector;

class JobExecutor extends Executor
{
    /** @var array */
    protected $supportedJobs = [
        ProcessorRegistry::TYPE_IMPORT => [
            OrderConnector::IMPORT_JOB_NAME,
            CartConnector::IMPORT_JOB_NAME,
            CustomerConnector::IMPORT_JOB_NAME
        ]
    ];

    /** @var array */
    protected $bundleConfiguration;

    /**
     * {@inheritdoc}
     *
     * @param array $bundleConfiguration
     */
    public function __construct(
        ConnectorRegistry $jobRegistry,
        DoctrineJobRepository $batchJobRepository,
        ContextRegistry $contextRegistry,
        ManagerRegistry $managerRegistry,
        array $bundleConfiguration
    ) {
        parent::__construct($jobRegistry, $batchJobRepository, $contextRegistry, $managerRegistry);

        $this->bundleConfiguration = $bundleConfiguration;
    }

    /**
     * {@inheritdoc}
     */
    public function executeJob($jobType, $jobName, array $configuration = [])
    {
        if (
            empty($configuration[ProcessorRegistry::TYPE_IMPORT][InitialSyncProcessor::INITIAL_SYNCED_TO])
            ||
            empty($configuration[ProcessorRegistry::TYPE_IMPORT][InitialSyncProcessor::START_SYNC_DATE])
        ) {
            return parent::executeJob($jobType, $jobName, $configuration);
        }

        /** @var \DateTime $startSyncDate */
        $startSyncDate = $configuration[ProcessorRegistry::TYPE_IMPORT][InitialSyncProcessor::START_SYNC_DATE];

        /** @var \DateTime $initialSyncedTo */
        $initialSyncedTo = $configuration[ProcessorRegistry::TYPE_IMPORT][InitialSyncProcessor::INITIAL_SYNCED_TO];

        $jobResult = null;
        $interval = $this->getSyncInterval();

        $configuration[ProcessorRegistry::TYPE_IMPORT][InitialSyncProcessor::INTERVAL] = $interval;
        // Workaround for magento 1.6 support
        $initialSyncedTo->add($interval);

        while ($startSyncDate < $initialSyncedTo) {
            $jobResult = parent::executeJob($jobType, $jobName, $configuration);

            $initialSyncedTo->sub($interval);
            $configuration[ProcessorRegistry::TYPE_IMPORT][InitialSyncProcessor::INITIAL_SYNCED_TO] = $initialSyncedTo;
        }

        return $jobResult;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable($jobType, $jobName)
    {
        return false;

        if (empty($this->supportedJobs[$jobType])) {
            return false;
        }

        return in_array($jobName, $this->supportedJobs[$jobType], true);
    }

    /**
     * @return \DateInterval
     */
    protected function getSyncInterval()
    {
        if (empty($this->bundleConfiguration['sync_settings']['initial_import_step_interval'])) {
            throw new \InvalidArgumentException('Option "initial_import_step_interval" is missing');
        }

        $syncInterval = $this->bundleConfiguration['sync_settings']['initial_import_step_interval'];
        $interval = \DateInterval::createFromDateString($syncInterval);

        return $interval;
    }
}
