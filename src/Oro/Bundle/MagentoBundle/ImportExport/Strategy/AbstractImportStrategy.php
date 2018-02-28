<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Strategy;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\ExecutionContext;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Oro\Bundle\ChannelBundle\ImportExport\Helper\ChannelHelper;
use Oro\Bundle\ChannelBundle\Model\ChannelAwareInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\ImportExport\Helper\DefaultOwnerHelper;
use Oro\Bundle\MagentoBundle\Entity\IntegrationAwareInterface;
use Oro\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper\AddressImportHelper;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractImportStrategy extends DefaultMagentoImportStrategy implements
    LoggerAwareInterface,
    StepExecutionAwareInterface
{
    const CONTEXT_POST_PROCESS_IDS = 'postProcessIds';

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
     * @var ChannelHelper
     */
    protected $channelHelper;

    /**
     * @var AddressImportHelper
     */
    protected $addressHelper;

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
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * @param ChannelHelper $channelHelper
     */
    public function setChannelHelper(ChannelHelper $channelHelper)
    {
        $this->channelHelper = $channelHelper;
    }

    /**
     * @param AddressImportHelper $addressHelper
     */
    public function setAddressHelper(AddressImportHelper $addressHelper)
    {
        $this->addressHelper = $addressHelper;
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

    /**
     * {@inheritdoc}
     */
    protected function afterProcessEntity($entity)
    {
        if ($entity instanceof ChannelAwareInterface
            && $entity instanceof IntegrationAwareInterface
            && $entity->getChannel()
        ) {
            $dataChannel = $this->channelHelper->getChannel($entity->getChannel());
            if ($dataChannel) {
                $entity->setDataChannel($dataChannel);
            }
        }

        return parent::afterProcessEntity($entity);
    }

    /**
     * @param string $contextKey
     * @param mixed $dataToAppend
     */
    protected function appendDataToContext($contextKey, $dataToAppend)
    {
        $data = (array)$this->getExecutionContext()->get($contextKey);
        $data[] = $dataToAppend;
        $this->getExecutionContext()->put($contextKey, $data);
    }
}
