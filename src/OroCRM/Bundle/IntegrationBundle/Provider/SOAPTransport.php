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
     * @param array $settings
     * @return bool|mixed
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
     * @param $action
     * @param $params
     * @return mixed
     */
    protected function call($action, $params = [])
    {
        return $this->client->__soapCall($action, $params);
    }
}
