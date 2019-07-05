<?php

namespace Oro\Bundle\MagentoBundle\Provider\Transport\Provider;

use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestClientInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;
use Oro\Bundle\MagentoBundle\Model\OroBridgeExtension\Config;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Incapsulate logic of requesting information form Magento extension
 */
class OroBridgeExtensionConfigProvider
{
    const REST_CONFIG_URI = 'oro/ping';

    /** @var  Config */
    protected $config;

    /** @var PropertyAccess */
    protected $accessor;

    public function __construct()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * Use to clear cached data of config
     */
    public function clearCache()
    {
        $this->config = null;
    }

    /**
     * Performs ping request to the Magento server and returns Config object with server information
     *
     * @param RestClientInterface $client REST client
     * @param array $headers REST client headers with auth token
     *
     * @return Config
     *
     * @throws RestException in case of REST client fail to connect
     */
    public function getConfig(RestClientInterface $client, $headers)
    {
        if (null === $this->config) {
            try {
                $data = $client->get(static::REST_CONFIG_URI, [], $headers)->json();
                $this->processData($data);
            } catch (RestException $e) {
                if (Response::HTTP_NOT_FOUND === $e->getCode()) {
                    $this->initDefaultConfig();
                } else {
                    throw $e;
                }
            }
        }

        return $this->config;
    }

    /**
     * Creates new config and init property with it
     *
     * @return $this
     */
    protected function initDefaultConfig()
    {
        $this->config = new Config();

        return $this;
    }

    /**
     * Fill config with response data
     *
     * @param $data
     */
    protected function processData($data)
    {
        $this->initDefaultConfig();

        $this->config->setExtensionVersion($this->accessor->getValue($data, '[version]'));
        $this->config->setMagentoVersion($this->accessor->getValue($data, '[mage_version]'));
        $this->config->setAdminUrl($this->accessor->getValue($data, '[admin_url]'));
        $this->config->setCustomerScope($this->accessor->getValue($data, '[customer_scope]'));
    }
}
