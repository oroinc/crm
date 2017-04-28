<?php

namespace Oro\Bundle\MagentoBundle\Provider\Transport;

use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestResponseInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;

use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Exception\InvalidConfigurationException;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Transport\AbstractRestTransport;
use Oro\Bundle\IntegrationBundle\Provider\PingableInterface;
use Oro\Bundle\MagentoBundle\Provider\RestTokenProvider;

class RestTransport extends AbstractRestTransport implements
    MagentoTransportInterface,
    ServerTimeAwareInterface,
    PingableInterface,
    LoggerAwareInterface
{
    use LoggerAwareTrait;

    const API_URL_PREFIX = 'rest/V1';
    const TOKEN_KEY = 'api_token';

    const TOKEN_HEADER_KEY = 'Authorization';
    const TOKEN_MASK = 'Bearer %s';

    const UNAUTHORIZED_STATUS_CODE = '401';

    protected $headers = [];
    protected $restTokenProvider;

    /**
     * @param RestTokenProvider $restTokenProvider
     */
    public function __construct(RestTokenProvider $restTokenProvider)
    {
        $this->restTokenProvider = $restTokenProvider;
    }

    /**
     * @inheritDoc
     */
    public function init(Transport $transportEntity)
    {
        parent::init($transportEntity);
        $settings = $transportEntity->getSettingsBag();
        $token = $settings->get(static::TOKEN_KEY, false);
        if (false !== $token) {
            $token = $this->refreshToken();
        }
        $this->updateTokenHeaderParam($token);
    }

    /**
     * @param $token
     */
    protected function updateTokenHeaderParam($token)
    {
        $this->headers[static::TOKEN_HEADER_KEY] = $this->getTokenForHeader($token);
    }

    /**
     * @return array
     */
    protected function refreshToken()
    {
        return $this->restTokenProvider->getToken($this->settings, $this->getClient());
    }

    /**
     * @inheritDoc
     */
    protected function getClientBaseUrl(ParameterBag $parameterBag)
    {
        return rtrim($parameterBag->get('api_url'), '/') . '/' . ltrim(static::API_URL_PREFIX, '/');
    }

    /**
     * @inheritDoc
     */
    protected function getClientOptions(ParameterBag $parameterBag)
    {
        return [
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ];
    }

    /**
     * @param string $token
     *
     * @return string
     */
    protected function getTokenForHeader($token)
    {
        return sprintf(static::TOKEN_MASK, $token);
    }

    /**
     * @inheritDoc
     */
    public function call($action, $params = [], $method = 'get')
    {
        /**
         * @todo Create Magento client that will extends Guzzle and implements
         *       multi-attempts functionality
         */
        $allowedHttpRequests = ['get', 'post', 'delete', 'put'];
        if ($this->logger) {
            $this->logger->debug(
                sprintf(
                    '[%.1fMB/%.1fMB] Call %s action with http method %s with %s parameters',
                    memory_get_usage() / 1024 / 1024,
                    memory_get_peak_usage() / 1024 / 1024,
                    $action,
                    $method,
                    json_encode($params)
                )
            );
        }

        /**
         * @todo Make list extandable
         */
        if (!in_array($method, $allowedHttpRequests)) {
            throw new \Exception('Not supported http method !');
        }

        $response = $this->getClient()->{$method}();
        if (!$response instanceof RestResponseInterface) {
            throw new \Exception('Not supported http method !');
        }

        $lastResponse = $this->getClient()->getLastResponse();

        if (401 === $response->getStatusCode() && (null === $lastResponse || 401 !== $lastResponse->getStatusCode())) {
            $token = $this->refreshToken();
            $this->updateTokenHeaderParam($token);
            return $this->call($action, $params, $method);
        }

        return $response->json();
    }

    /**
     * @inheritDoc
     */
    public function isExtensionInstalled()
    {
        // TODO: Implement isExtensionInstalled() method.
    }

    /**
     * @inheritDoc
     */
    public function getAdminUrl()
    {
        // TODO: Implement getAdminUrl() method.
    }

    /**
     * @inheritDoc
     */
    public function getOrders()
    {
        // TODO: Implement getOrders() method.
    }

    /**
     * @inheritDoc
     */
    public function getCarts()
    {
        // TODO: Implement getCarts() method.
    }

    /**
     * @inheritDoc
     */
    public function getCustomers()
    {
        // TODO: Implement getCustomers() method.
    }

    /**
     * @inheritDoc
     */
    public function getCustomerGroups()
    {
        // TODO: Implement getCustomerGroups() method.
    }

    /**
     * @inheritDoc
     */
    public function getStores()
    {
        // TODO: Implement getStores() method.
    }

    /**
     * @inheritDoc
     */
    public function getWebsites()
    {
        // TODO: Implement getWebsites() method.
    }

    /**
     * @inheritDoc
     */
    public function getRegions()
    {
        // TODO: Implement getRegions() method.
    }

    /**
     * @inheritDoc
     */
    public function getCustomerInfo($originId)
    {
        // TODO: Implement getCustomerInfo() method.
    }

    /**
     * @inheritDoc
     */
    public function getCustomerAddresses($originId)
    {
        // TODO: Implement getCustomerAddresses() method.
    }

    /**
     * @inheritDoc
     */
    public function getErrorCode(\Exception $e)
    {
        // TODO: Implement getErrorCode() method.
    }

    /**
     * @inheritDoc
     */
    public function getOrderInfo($incrementId)
    {
        // TODO: Implement getOrderInfo() method.
    }

    /**
     * @inheritDoc
     */
    public function createCustomer(array $customerData)
    {
        // TODO: Implement createCustomer() method.
    }

    /**
     * @inheritDoc
     */
    public function updateCustomer($customerId, array $customerData)
    {
        // TODO: Implement updateCustomer() method.
    }

    /**
     * @inheritDoc
     */
    public function createCustomerAddress($customerId, array $item)
    {
        // TODO: Implement createCustomerAddress() method.
    }

    /**
     * @inheritDoc
     */
    public function updateCustomerAddress($customerAddressId, array $item)
    {
        // TODO: Implement updateCustomerAddress() method.
    }

    /**
     * @inheritDoc
     */
    public function getCustomerAddressInfo($customerAddressId)
    {
        // TODO: Implement getCustomerAddressInfo() method.
    }

    /**
     * @inheritDoc
     */
    public function getNewsletterSubscribers()
    {
        // TODO: Implement getNewsletterSubscribers() method.
    }

    /**
     * @inheritDoc
     */
    public function createNewsletterSubscriber(array $subscriberData)
    {
        // TODO: Implement createNewsletterSubscriber() method.
    }

    /**
     * @inheritDoc
     */
    public function updateNewsletterSubscriber($subscriberId, array $subscriberData)
    {
        // TODO: Implement updateNewsletterSubscriber() method.
    }

    /**
     * @inheritDoc
     */
    public function isSupportedExtensionVersion()
    {
        // TODO: Implement isSupportedExtensionVersion() method.
    }

    /**
     * @inheritDoc
     */
    public function getExtensionVersion()
    {
        // TODO: Implement getExtensionVersion() method.
    }

    /**
     * @inheritDoc
     */
    public function getMagentoVersion()
    {
        // TODO: Implement getMagentoVersion() method.
    }

    /**
     * @inheritDoc
     */
    public function getServerTime()
    {
        // TODO: Implement getServerTime() method.
    }

    /**
     * @inheritDoc
     */
    public function getLabel()
    {
        // TODO: Implement getLabel() method.
    }

    /**
     * @inheritDoc
     */
    public function getSettingsFormType()
    {
        // TODO: Implement getSettingsFormType() method.
    }

    /**
     * @inheritDoc
     */
    public function getSettingsEntityFQCN()
    {
        // TODO: Implement getSettingsEntityFQCN() method.
    }

    /**
     * @inheritDoc
     */
    public function ping()
    {
        // TODO: Implement ping() method.
    }
}
