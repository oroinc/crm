<?php

namespace Oro\Bundle\MagentoBundle\Provider\Transport;

use FOS\RestBundle\Util\Codes;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;

use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Exception\RuntimeException;
use Oro\Bundle\IntegrationBundle\Provider\PingableInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestResponseInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestClientInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\BridgeRestClientFactory;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Provider\RestTokenProvider;
use Oro\Bundle\MagentoBundle\Provider\RestPingProvider;
use Oro\Bundle\MagentoBundle\Provider\Iterator\Rest\BaseMagentoRestIterator;

class RestTransport implements
    TransportInterface,
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

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var Transport
     */
    protected $transportEntity;

    /**
     * @var RestClientInterface
     */
    protected $client;

    /**
     * @var BridgeRestClientFactory
     */
    protected $clientFactory;

    /**
     * @var RestTokenProvider
     */
    protected $restTokenProvider;

    /**
     * @var RestPingProvider
     */
    protected $pingProvider;

    /**
     * @param BridgeRestClientFactory $clientFactory
     * @param RestTokenProvider       $restTokenProvider
     */
    public function __construct(
        BridgeRestClientFactory $clientFactory,
        RestTokenProvider $restTokenProvider,
        RestPingProvider $pingProvider

    ) {
        $this->clientFactory = $clientFactory;
        $this->restTokenProvider = $restTokenProvider;
        $this->pingProvider = $pingProvider;
    }

    /**
     * @inheritDoc
     */
    public function init(Transport $transportEntity)
    {
        $this->transportEntity = $transportEntity;
        $this->client = $this->clientFactory->createRestClient($transportEntity);
        $settings = $transportEntity->getSettingsBag();
        $token = $settings->get(static::TOKEN_KEY, false);
        if (false === $token) {
            $token = $this->refreshToken();
        }
        $this->updateTokenHeaderParam($token);

        $this->pingProvider->setClient($this->client);
        $this->pingProvider->setHeaders($this->headers);
    }

    /**
     * @param RestException $exception
     *
     * @return bool
     */
    protected function isUnauthorizedException(RestException $exception)
    {
        return $exception->getResponse()->getStatusCode() === Codes::HTTP_UNAUTHORIZED;
    }

    /**
     * @return bool
     */
    protected function isAllowToProcessUnauthorizedResponse()
    {
        $lastResponse = $this->client->getLastResponse();
        return null === $lastResponse || $lastResponse->getStatusCode() !== Codes::HTTP_UNAUTHORIZED;
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
        return $this->restTokenProvider->getToken($this->transportEntity, $this->client);
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
    public function isExtensionInstalled()
    {
        try {
            return $this->pingProvider->isExtensionInstalled();
        } catch (RestException $e) {
            return $this->handleException($e, 'isExtensionInstalled');
        }
    }

    /**
     * @inheritDoc
     */
    public function getAdminUrl()
    {
        try {
            return $this->pingProvider->getAdminUrl();
        } catch (RestException $e) {
            return $this->handleException($e, 'getAdminUrl');
        }
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
        //TODO: Implement CustomerRestIterator
        return new BaseMagentoRestIterator($this);
    }

    /**
     * {@inheritdoc}
     */
    public function isCustomerHasUniqueEmail(Customer $customer)
    {
        /**
         * Will be implemented with method `getCustomers`
         */
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
        try {
            return $this->pingProvider->getBridgeVersion();
        } catch (RestException $e) {
            return $this->handleException($e, 'getExtensionVersion');
        }
    }

    /**
     * @inheritDoc
     */
    public function getMagentoVersion()
    {
        try {
            return $this->pingProvider->getMagentoVersion();
        } catch (RestException $e) {
            return $this->handleException($e, 'getMagentoVersion');
        }
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
        return $this->pingProvider->ping();
    }

    /**
     * @param RestException $exception
     * @param string        $methodName
     * @param array         $arguments
     *
     * @return mixed
     */
    protected function handleException(RestException $exception, $methodName, $arguments = [])
    {
        if ($exception->getResponse() instanceof RestResponseInterface &&
            $this->isUnauthorizedException($exception) &&
            $this->isAllowToProcessUnauthorizedResponse()
        ) {
            /**
             * Update token and do request one more time
             */
            $token = $this->refreshToken();
            $this->updateTokenHeaderParam($token);
            return call_user_func_array([$this, $methodName], $arguments);
        }

        throw new RuntimeException(
            sprintf(
                'Server returned unexpected response. Response code %s',
                $exception->getCode()
            )
        );
    }
}
