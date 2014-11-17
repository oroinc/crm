<?php

namespace OroCRM\Bundle\CampaignBundle\Provider;

use OroCRM\Bundle\CampaignBundle\Transport\TransportInterface;
use OroCRM\Bundle\CampaignBundle\Transport\VisibilityTransportInterface;

class EmailTransportProvider
{
    /**
     * @var array
     */
    protected $transports = [];

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

    /**
     * @return array
     */
    public function getVisibleTransportChoices()
    {
        $choices = [];
        foreach ($this->getTransports() as $transport) {
            if ($this->isVisibleInForm($transport)) {
                $choices[$transport->getName()] = $transport->getLabel();
            }
        }
        return $choices;
    }

    /**
     * @param TransportInterface $transport
     * @return bool
     */
    protected function isVisibleInForm($transport)
    {
        return !$transport instanceof VisibilityTransportInterface || $transport->isVisibleInForm();
    }
}
