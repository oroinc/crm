<?php

namespace Oro\Bundle\MagentoBundle\Provider\Transport\Provider;

use FOS\RestBundle\Util\Codes;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestClientInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;
use Oro\Bundle\MagentoBundle\Model\OroBridgeExtension\Config;

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

    /** @var RestClientInterface */
    protected $client;

    public function __construct()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * Inits REST client for provider. This method should be called before any other public method
     *
     * @param RestClientInterface $client
     */
    public function setClient(RestClientInterface $client)
    {
        $this->client = $client;
        $this->config = null;
    }

    /**
     * Performs ping request to the Magento server and returns Config object with server information
     *
     * @param array $headers REST client headers with auth token
     * @param bool $force if false then only first call will cause request to Magento server
     *
     * @return Config
     *
     * @throws RestException in case of REST client fail to connect
     */
    public function getConfig($headers, $force = false)
    {
        if (null === $this->config || $force) {
            try {
                $data = $this->client->get(static::REST_CONFIG_URI, [], $headers)->json();
                $this->processData($data);
            } catch (RestException $e) {
                if (Codes::HTTP_NOT_FOUND === $e->getCode()) {
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
