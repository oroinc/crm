<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Writer;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\ImportExport\Writer\PersistentBatchWriter;
use Oro\Bundle\MagentoBundle\Entity\OriginAwareInterface;
use Oro\Bundle\MagentoBundle\Provider\Strategy\TwoWaySyncStrategyInterface;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;

abstract class AbstractExportWriter extends PersistentBatchWriter
{
    /** @var MagentoTransportInterface */
    protected $transport;

    /** @var string */
    protected $channelClassName;

    /**
     * @var TwoWaySyncStrategyInterface
     */
    protected $strategy;

    /**
     * @param TwoWaySyncStrategyInterface $strategy
     */
    public function setStrategy(TwoWaySyncStrategyInterface $strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * @param MagentoTransportInterface $transport
     */
    public function setTransport(MagentoTransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @param string $channelClassName
     */
    public function setChannelClassName($channelClassName)
    {
        $this->channelClassName = $channelClassName;
    }

    /**
     * @return TwoWaySyncStrategyInterface
     */
    public function getStrategy()
    {
        if (!$this->strategy) {
            throw new \InvalidArgumentException('Strategy is missing');
        }

        $this->strategy->setStepExecution($this->stepExecution);

        return $this->strategy;
    }

    /**
     * @return array
     */
    protected function getEntityChangeSet()
    {
        return (array)$this->getContext()->getOption('changeSet');
    }

    /**
     * @return string
     */
    protected function getTwoWaySyncStrategy()
    {
        return (string)$this->getContext()->getOption('twoWaySyncStrategy');
    }

    /**
     * @return Channel
     */
    protected function getChannel()
    {
        if (!$this->getContext()->hasOption('channel')) {
            throw new \InvalidArgumentException('Channel id is missing');
        }

        $channelId = $this->getContext()->getOption('channel');
        $channel = $this->registry->getRepository($this->channelClassName)->find($channelId);

        if (!$channel) {
            throw new \InvalidArgumentException('Channel is missing');
        }

        return $channel;
    }

    /**
     * @return ContextInterface
     */
    protected function getContext()
    {
        return $this->contextRegistry->getByStepExecution($this->stepExecution);
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        if (!$this->transport) {
            throw new \InvalidArgumentException('Transport was not provided');
        }

        parent::write($items);
    }

    /**
     * @return OriginAwareInterface
     */
    protected function getEntity()
    {
        if (!$this->getContext()->hasOption('entity')) {
            throw new \InvalidArgumentException('Option "entity" was not configured');
        }

        $entity = $this->getContext()->getOption('entity');

        if (!$entity) {
            throw new \InvalidArgumentException('Missing entity in context');
        }

        if (!$entity instanceof OriginAwareInterface) {
            throw new \InvalidArgumentException('Entity does not implements OriginAwareInterface');
        }

        return $entity;
    }
}
