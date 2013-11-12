<?php

namespace OroCRM\Bundle\IntegrationBundle\Provider\Magento;

use OroCRM\Bundle\IntegrationBundle\Provider\SOAPTransport;

/**
 * Magento SOAP transport
 * used to fetch and pull data to/from Magento instance
 * with sessionId param
 *
 * @package OroCRM\Bundle\IntegrationBundle\Provider\Magento
 */
class MageSoapTransport extends SOAPTransport
{
    /** @var string */
    protected $sessionId;

    /**
     * Init transport and retrieve sessionId for use in subsequent requests
     *
     * @param array $settings
     * @return bool|mixed
     */
    public function init(array $settings)
    {
        $apiKey = $settings['api_key'];
        $apiUser = $settings['api_user'];

        if (!parent::init($settings)) {
            return false;
        }

        /** @var string sessionId returned by Magento API login method */
        $this->sessionId = $this->client->login($apiUser, $apiKey);

        return true;
    }

    /**
     * @param $action
     * @param $params
     * @return mixed
     */
    public function call($action, $params = [])
    {
        return parent::call($action, [$this->sessionId, $params]);
    }
}
