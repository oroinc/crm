<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use OroCRM\Bundle\IntegrationBundle\Provider\SOAPTransport;

/**
 * Magento SOAP transport
 * used to fetch and pull data to/from Magento instance
 * with sessionId param using SOAP requests
 *
 * @package OroCRM\Bundle\MagentoBundle
 */
class MageSoapTransport extends SOAPTransport
{
    /** @var string */
    protected $sessionId;

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function call($action, $params = [])
    {
        return parent::call($action, [$this->sessionId, $params]);
    }
}
