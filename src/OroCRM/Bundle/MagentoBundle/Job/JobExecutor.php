<?php

namespace OroCRM\Bundle\MagentoBundle\Job;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;

use Oro\Bundle\IntegrationBundle\ImportExport\Job\Executor;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;

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
    protected function doJob(JobInstance $jobInstance, JobExecution $jobExecution)
    {
        $jobResults = [];

        $context = $jobExecution->getExecutionContext();
        $startDate = $context->get('startDate');
        $syncStartDate = $context->get('syncStartDate');

        while ($startDate >= $syncStartDate) {
            $jobResults[] = parent::doJob($jobInstance, $jobExecution);

            /** @todo: config and to date */
            $startDate->modify('-1 day');
            $context->put('syncStartDate', $startDate);
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

        return !empty($jobs[$jobType][$jobName]);
    }
}
