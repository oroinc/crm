<?php

namespace OroCRM\Bundle\IntegrationBundle\Provider;

abstract class AbstractConnector implements ConnectorInterface
{
    /** @var TransportInterface */
    protected $transport;

    /** @var ChannelTypeInterface */
    protected $channel;

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
        return $this->transport->init($this->channel->getSettings());
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
        return $this->transport->call($action, $params);
    }
}
