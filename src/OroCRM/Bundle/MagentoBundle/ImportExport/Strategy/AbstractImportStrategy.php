<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\ExecutionContext;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\ImportExport\Helper\DefaultOwnerHelper;
use OroCRM\Bundle\MagentoBundle\Entity\IntegrationAwareInterface;

abstract class AbstractImportStrategy extends ConfigurableAddOrReplaceStrategy implements
    LoggerAwareInterface,
    StepExecutionAwareInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var DefaultOwnerHelper
     */
    protected $ownerHelper;

    /**
     * @var StepExecution
     */
    protected $stepExecution;

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param DefaultOwnerHelper $ownerHelper
     */
    public function setOwnerHelper($ownerHelper)
    {
        $this->ownerHelper = $ownerHelper;
    }

    /**
     * @param StepExecution $stepExecution
     * @return AbstractImportStrategy
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    protected function beforeProcessEntity($entity)
    {
        if ($entity instanceof IntegrationAwareInterface) {
            /** @var Channel $channel */
            $channel = $this->databaseHelper->getEntityReference($entity->getChannel());
            $this->ownerHelper->populateChannelOwner($entity, $channel);
        }

        return parent::beforeProcessEntity($entity);
    }

    /**
     * @return ExecutionContext
     */
    protected function getExecutionContext()
    {
        if (!$this->stepExecution) {
            throw new \InvalidArgumentException('Execution context is not configured');
        }

        return $this->stepExecution->getJobExecution()->getExecutionContext();
    }
}
