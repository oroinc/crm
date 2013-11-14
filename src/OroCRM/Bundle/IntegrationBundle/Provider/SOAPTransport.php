<?php

namespace OroCRM\Bundle\IntegrationBundle\Provider;

/**
 * @package OroCRM\Bundle\IntegrationBundle
 */
class SOAPTransport implements TransportInterface
{
    /** @var \SoapClient */
    protected $client;

    /**
     * {@inheritdoc}
     */
    public function init(array $settings)
    {
        if (!empty($settings['wsdl_url'])) {
            $this->client = new \SoapClient($settings['wsdl_url']);
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function call($action, $params = [])
    {
        return $this->client->__soapCall($action, $params);
    }
}
