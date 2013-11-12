<?php

namespace OroCRM\Bundle\IntegrationBundle\Provider;

/**
 * Magento API transport
 * used to fetch and pull data to/from Magento instance
 *
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
    public function connect(array $settings)
    {
        if (!empty($settings['wsdl_url'])) {
            $this->client = new \SoapClient($settings['wsdl_url']);
            return true;
        }

        return false;
    }

    /**
     * @param $action
     * @param array $params
     * @return mixed|void
     */
    public function fetch($action, $params = [])
    {
        return $this->call($action, $params);
    }

    /**
     * @param $action
     * @param array $params
     * @return mixed|void
     */
    public function send($action, $params = [])
    {
        return $this->call($action, $params);
    }

    /**
     * @param $action
     * @param $params
     * @return mixed
     */
    protected function call($action, $params)
    {
        return $this->client->__soapCall($action, $params);
    }
}
