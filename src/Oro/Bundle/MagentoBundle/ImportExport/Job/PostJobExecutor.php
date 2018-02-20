<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Job;

use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Oro\Bundle\ImportExportBundle\Job\JobExecutor;
use Oro\Bundle\ImportExportBundle\Serializer\Serializer;

class PostJobExecutor extends JobExecutor
{
    /**
     * {@inheritdoc}
     */
    protected function createJobExecution(array $configuration, JobInstance $jobInstance)
    {
        $jobExecution = parent::createJobExecution($configuration, $jobInstance);

        /**
         * Channel and processor alias should be added to the execution context to be available in post processing jobs
         */
        $shareKeys = ['channel', Serializer::PROCESSOR_ALIAS_KEY];
        foreach ($shareKeys as $key) {
            if (isset($configuration[$key])) {
                $jobExecution->getExecutionContext()->put($key, $configuration[$key]);
            }
        }

        return $jobExecution;
    }
}
