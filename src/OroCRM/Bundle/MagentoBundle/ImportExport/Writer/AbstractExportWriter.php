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

    protected function initTransport()
    {
        $channelId = $this->getContext()->getOption('channel');

        if (!$channelId) {
            throw new \InvalidArgumentException('Channel is missing');
        }

        /** @var Channel $channel */
        $channel = $this->registry->getRepository($this->channelClassName)->find($channelId);
        if (!$channel) {
            throw new \InvalidArgumentException('Channel is missing');
        }

        $this->transport->init($channel->getTransport());
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
}
