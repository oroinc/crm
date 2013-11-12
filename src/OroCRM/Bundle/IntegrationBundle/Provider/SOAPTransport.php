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

    /** @var string */
    protected $sessionId;

    /** @var string */
    protected $lastError;

    /**
     * @param array $settings
     * @return bool|mixed
     */
    public function connect(array $settings)
    {
        $wsdlUrl = $settings['wsdl_url'];
        $apiKey = $settings['api_key'];
        $apiUser = $settings['api_user'];

        $this->client = new \SoapClient($wsdlUrl);

        try {
            $this->sessionId = $this->client->login($apiUser, $apiKey);
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }

        return true;
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
        return $this->client->call($this->sessionId, $action, $params);
    }
}
