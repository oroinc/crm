<?php

namespace OroCRM\Bundle\IntegrationBundle\Provider;

abstract class AbstractConnector implements ConnectorInterface
{
    /** @var TransportInterface */
    protected $transport;

    /** @var ChannelTypeInterface */
    protected $channel = null;

    /** @var bool */
    protected $isConnected = false;

    /** @var SyncProcessorInterface[] */
    protected $processors;

    /**
     * @param TransportInterface $transport
     */
    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * {@inheritdoc}
     */
    public function connect()
    {
        if ($this->isConnected) {
            return true;
        }

        if (is_null($this->channel)) {
            throw new \Exception('There\'s no configured channel in connector');
        }

        $this->isConnected = $this->transport->init($this->channel->getSettings());

        return $this->isConnected;
    }

    /**
     * Used to get/send data from/to remote channel using transport
     *
     * @param string $action
     * @param array $params
     * @return mixed
     */
    protected function call($action, $params = [])
    {
        if ($this->isConnected === false) {
            $this->connect();
        }

        return $this->transport->call($action, $params);
    }

    /**
     * @param ChannelTypeInterface $channel
     * @return $this
     */
    public function setChannel(ChannelTypeInterface $channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * @param SyncProcessorInterface $processor
     */
    public function addSyncProcessor(SyncProcessorInterface $processor)
    {
        $this->processors[] = $processor;
    }

    /**
     * Process batch in all registered sync processors
     *
     * @param mixed $batch
     * @return bool
     */
    public function processSyncBatch($batch)
    {
        /** $processor SyncProcessorInterface */
        foreach ($this->processors as $processor) {
            $result = $processor->process($batch);
        }

        return defined($result) && $result;
    }
}
