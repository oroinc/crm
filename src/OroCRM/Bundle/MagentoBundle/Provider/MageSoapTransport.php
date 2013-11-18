<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\HttpFoundation\ParameterBag;

use Oro\Bundle\IntegrationBundle\Provider\SOAPTransport;

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
    public function init(ParameterBag $settings)
    {
        $apiUser = $settings->get('api_user', false);
        $apiKey = $settings->get('api_key', false);

        if (!$apiUser || !$apiKey) {
            throw new InvalidConfigurationException(
                "Magento SOAP transport require 'api_key' and 'api_user' settings to be defined."
            );
        }


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
