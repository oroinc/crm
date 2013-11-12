<?php

namespace OroCRM\Bundle\IntegrationBundle\Provider\Magento;

use OroCRM\Bundle\IntegrationBundle\Provider\SOAPTransport;

class MageSoapTransport extends SOAPTransport
{
    /** @var string */
    protected $sessionId;

    /**
     * @param array $settings
     * @return bool|mixed
     */
    public function connect(array $settings)
    {
        $apiKey = $settings['api_key'];
        $apiUser = $settings['api_user'];

        if (!parent::connect($settings)) {
            return false;
        }

        $this->sessionId = $this->client->login($apiUser, $apiKey);

        return true;
    }

    /**
     * @param $action
     * @param $params
     * @return mixed
     */
    protected function call($action, $params)
    {
        return parent::call($action, [$this->sessionId, $params]);
    }
}
