<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\ExecutionContext;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

use Doctrine\Common\Util\ClassUtils;

use OroCRM\Bundle\ChannelBundle\Model\ChannelAwareInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\ImportExport\Helper\DefaultOwnerHelper;
use OroCRM\Bundle\ChannelBundle\ImportExport\Helper\ChannelHelper;
use OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper\AddressImportHelper;
use OroCRM\Bundle\MagentoBundle\Entity\IntegrationAwareInterface;
use OroCRM\Bundle\MagentoBundle\Entity\OriginAwareInterface;

abstract class AbstractImportStrategy extends ConfigurableAddOrReplaceStrategy implements
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
     * @return AbstractImportStrategy
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
    protected function validateAndUpdateContext($entity)
    {
        $this->saveOriginIdContext($entity);

        return parent::validateAndUpdateContext($entity);
    }

    /**
     * @param OriginAwareInterface $entity
     */
    protected function saveOriginIdContext($entity)
    {
        if ($entity instanceof OriginAwareInterface) {
            /** @var OriginAwareInterface $entity */
            $postProcessIds = (array)$this->getExecutionContext()->get(self::CONTEXT_POST_PROCESS_IDS);
            $postProcessIds[ClassUtils::getClass($entity)][] = $entity->getOriginId();
            $this->getExecutionContext()->put(self::CONTEXT_POST_PROCESS_IDS, $postProcessIds);
        }
    }

    /**
     * @param ChannelAwareInterface|IntegrationAwareInterface $entity
     */
    protected function processDataChannel($entity)
    {
        if ($entity->getChannel()) {
            $dataChannel = $this->channelHelper->getChannel($entity->getChannel());
            if ($dataChannel) {
                $entity->setDataChannel($dataChannel);
            }
        }
    }
}
