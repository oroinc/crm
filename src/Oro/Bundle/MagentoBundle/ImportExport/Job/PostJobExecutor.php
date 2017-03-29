<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Job;

use Akeneo\Bundle\BatchBundle\Entity\JobInstance;

use Oro\Bundle\ImportExportBundle\Job\JobExecutor;

class PostJobExecutor extends JobExecutor
{
    /**
     * {@inheritdoc}
     */
    protected function createJobExecution(array $configuration, JobInstance $jobInstance)
    {
        $jobExecution = parent::createJobExecution($configuration, $jobInstance);

        /**
         * Channel should be added to the execution context to be available in post processing jobs
         */
        if (isset($configuration['channel'])) {
            $jobExecution->getExecutionContext()->put('channel', $configuration['channel']);
        }

        return $jobExecution;
    }
}
