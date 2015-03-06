<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Writer;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\ImportExport\Writer\PersistentBatchWriter;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;

abstract class AbstractExportWriter extends PersistentBatchWriter
{
    /** @var MagentoTransportInterface */
    protected $transport;

    /** @var string */
    protected $channelClassName;

    /** @var string */
    protected $entityClassName;

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
     * @param string $entityClassName
     */
    public function setEntityClassName($entityClassName)
    {
        $this->entityClassName = $entityClassName;
    }

    /**
     * @return Channel
     */
    protected function getChannel()
    {
        $channelId = $this->getContext()->getOption('channel');

        if (!$channelId) {
            throw new \InvalidArgumentException('Channel id is missing');
        }

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
     * @return object
     */
    protected function getEntity()
    {
        if (!$this->getContext()->hasOption('entity')) {
            throw new \InvalidArgumentException('Option "entity" was not configured');
        }

        if (!$this->getContext()->hasOption('entityName')) {
            throw new \InvalidArgumentException('Option "entityName" was not configured');
        }

        if (!$this->entityClassName) {
            throw new \InvalidArgumentException('Class name was not configured');
        }

        $entity = $this->getContext()->getOption('entity');

        if (!$entity || !is_a($entity, $this->entityClassName, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Instance of "%s" expected, "%s" given',
                    $this->entityClassName,
                    is_object($entity) ? get_class($entity) : gettype($entity)
                )
            );
        }

        return $entity;
    }
}
