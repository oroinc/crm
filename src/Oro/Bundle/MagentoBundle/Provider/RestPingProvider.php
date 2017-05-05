<?php

namespace Oro\Bundle\MagentoBundle\Provider;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

use FOS\RestBundle\Util\Codes;

use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestClientInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;

use Oro\Bundle\MagentoBundle\Exception\RuntimeException;

class RestPingProvider implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    const REST_PING_URI                   = 'oro/ping';
    const CUSTOMER_SHARING_GLOBAL         = 0;
    const CUSTOMER_SHARING_PER_WEBSITE    = 1;

    /** @var string  */
    protected $magentoVersion;

    /** @var string */
    protected $bridgeVersion;

    /** @var string */
    protected $adminUrl;

    /** @var bool */
    protected $isExtensionInstalled;

    /** @var string */
    protected $customerScope;

    /** @var RestClientInterface  */
    protected $client;

    /** @var string[]  */
    protected $headers = [];

    /** @var string[] */
    protected $params = [];

    protected $pingResponseData = [
        'version'           => '',
        'mage_version'      => '',
        'admin_url'         => '',
        'customer_scope'    => ''
    ];

    /**
     * @param RestClientInterface $client
     * @return RestPingProvider
     */
    public function setClient(RestClientInterface $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return RestClientInterface
     * @throws RuntimeException
     */
    protected function getClient()
    {
        if (null === $this->client) {
            throw new RuntimeException("REST Transport isn't configured properly.");
        }

        return $this->client;
    }

    /**
     * @param string[] $headers
     * @return RestPingProvider
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @param string[] $params
     * @return RestPingProvider
     */
    public function setParams(array $params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * @throws RestException
     */
    protected function doRequest()
    {
        $this->logger->info('Do request to detect `OroBridge extension`');
        try {
            $response = $this->getClient()->get(self::REST_PING_URI, $this->params, $this->headers);
            $this->processResponse($response->json());
        } catch (RestException $e) {
            if (Codes::HTTP_NOT_FOUND === $e->getCode()) {
                /** if 404 error OroBridge isn't installed */
                $this->isExtensionInstalled = false;
            } else {
                $this->logger->error($e->getMessage());
                throw $e;
            }
        }
        $this->logger->info('Request  to detect `OroBridge extension` is done');
    }

    /**
     * @param array $responseData
     * @return void
     */
    protected function processResponse(array $responseData)
    {
        $this->logger->info('Process response data start');

        $responseData = array_merge($this->pingResponseData, $responseData);

        $this->isExtensionInstalled = !empty($responseData['version']);
        $this->bridgeVersion        = $this->isExtensionInstalled ? $responseData['version'] : '';
        $this->magentoVersion       = $responseData['mage_version'];
        $this->adminUrl             = $responseData['admin_url'];
        $this->customerScope        = $responseData['customer_scope'];

        $this->logger->info('Process response data end');
    }

    /**
     * @return bool
     */
    public function isCustomerSharingPerWebsite()
    {
        return ((int)$this->getCustomerScope() === static::CUSTOMER_SHARING_PER_WEBSITE);
    }

    /**
     * @return string
     */
    public function getCustomerScope()
    {
        if (null === $this->customerScope) {
            $this->doRequest();
        }

        return $this->customerScope;
    }

    /**
     * @return string
     */
    public function getMagentoVersion()
    {
        if (null === $this->magentoVersion) {
            $this->doRequest();
        }

        return $this->magentoVersion;
    }

    /**
     * @return string
     */
    public function getBridgeVersion()
    {
        if (null === $this->bridgeVersion) {
            $this->doRequest();
        }

        return $this->bridgeVersion;
    }

    /**
     * @return string
     */
    public function getAdminUrl()
    {
        if (null === $this->adminUrl) {
            $this->doRequest();
        }

        return $this->adminUrl;
    }

    /**
     * @return bool
     */
    public function isExtensionInstalled()
    {
        if (null === $this->isExtensionInstalled) {
            $this->doRequest();
        }

        return $this->isExtensionInstalled;
    }

    /**
     * @return bool
     */
    public function ping()
    {
        try {
            $this->getClient()->get(self::REST_PING_URI, $this->params, $this->headers);
            return true;
        } catch (RestException $e) {
            return false;
        }
    }

    /**
     * If we need reprocess data
     * do forceRequest
     *
     * @return RestPingProvider
     */
    public function forceRequest()
    {
        $this->doRequest();

        return $this;
    }
}
