<?php

namespace OroCRM\Bundle\CampaignBundle\Provider;

use OroCRM\Bundle\CampaignBundle\Transport\TransportInterface;

class EmailTransportProvider
{
    /**
     * @var array
     */
    protected $transports = array();

    /**
     * @param TransportInterface $transport
     */
    public function addTransport(TransportInterface $transport)
    {
        $this->transports[$transport->getName()] = $transport;
    }

    /**
     * @return TransportInterface[]
     */
    public function getTransports()
    {
        return $this->transports;
    }

    /**
     * @param string $name
     * @return TransportInterface
     */
    public function getTransportByName($name)
    {
        if ($this->hasTransport($name)) {
            return $this->transports[$name];
        } else {
            throw new \RuntimeException(sprintf('Transport %s is unknown', $name));
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasTransport($name)
    {
        return isset($this->transports[$name]);
    }
}
