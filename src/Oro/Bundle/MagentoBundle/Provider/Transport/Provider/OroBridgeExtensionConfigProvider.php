<?php

namespace Oro\Bundle\MagentoBundle\Provider\Transport\Provider;

use FOS\RestBundle\Util\Codes;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestClientInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;
use Oro\Bundle\MagentoBundle\Model\OroBridgeExtension\Config;

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
     * @param RestClientInterface $client
     */
    public function setClient(RestClientInterface $client)
    {
        $this->client = $client;
        $this->config = null;
    }

    /**
     * @param array $headers
     * @param bool $force
     *
     * @return Config
     *
     * @throws RestException
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

    protected function initDefaultConfig()
    {
        $this->config = new Config();

        return $this;
    }

    /**
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
