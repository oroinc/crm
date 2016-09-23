<?php

namespace Oro\Bundle\MagentoBundle\Service;

use Oro\Bundle\ImportExportBundle\Job\JobExecutor;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;

class InformationLoader
{
    /**
     * @var ConnectorInterface
     */
    protected $connector;

    /**
     * @var string
     */
    protected $processorAlias;

    /**
     * @var JobExecutor
     */
    protected $jobExecutor;

    /**
     * @param JobExecutor $jobExecutor
     * @param ConnectorInterface $connector
     * @param string $processorAlias
     */
    public function __construct(JobExecutor $jobExecutor, ConnectorInterface $connector, $processorAlias)
    {
        $this->jobExecutor = $jobExecutor;
        $this->connector = $connector;
        $this->processorAlias = $processorAlias;
    }

    /**
     * Execute import job to load information.
     *
     * @param Integration $channel
     * @param array $configuration
     * @return bool
     */
    public function load(Integration $channel, array $configuration = [])
    {
        $defaultConfiguration = [
            ProcessorRegistry::TYPE_IMPORT => [
                'processorAlias' => $this->processorAlias,
                'entityName' => $this->connector->getImportEntityFQCN(),
                'channel' => $channel->getId(),
                'channelType' => $channel->getType()
            ]
        ];
        $configuration = array_merge_recursive($defaultConfiguration, $configuration);
        $jobResult = $this->jobExecutor->executeJob(
            ProcessorRegistry::TYPE_IMPORT,
            $this->connector->getImportJobName(),
            $configuration
        );

        return $jobResult->isSuccessful();
    }
}
