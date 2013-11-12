<?php

namespace OroCRM\Bundle\IntegrationBundle\Provider;

abstract class AbstractConnector implements ConnectorInterface
{
    /** @var IntegrationTransportInterface */
    protected $transport;

    /** @var ChannelTypeInterface */
    protected $channel;

    /**
     * @param IntegrationTransportInterface $transport
     */
    public function __construct(IntegrationTransportInterface $transport)
    {
        $this->transport = $transport;
    }

    public function connect()
    {
        $this->transport->connect($this->channel->getSettings());
    }

    /**
     * Used to get data from remote channel using transport
     *
     * @return mixed
     */
    abstract protected function fetch();

    /**
     * Used to push data to remote channel using transport
     *
     * @return mixed
     */
    abstract protected function send();
}
