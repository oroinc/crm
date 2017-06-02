<?php

namespace Oro\Bundle\MagentoBundle\Provider\Transport;

use FOS\RestBundle\Util\Codes;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;

use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\MagentoBundle\Model\OroBridgeExtension\Config;
use Oro\Bundle\IntegrationBundle\Provider\PingableInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestResponseInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestClientInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\BridgeRestClientFactory;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\MagentoBundle\Form\Type\RestTransportSettingFormType;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Exception\ExtensionRequiredException;
use Oro\Bundle\MagentoBundle\Exception\RuntimeException;
use Oro\Bundle\MagentoBundle\Provider\RestTokenProvider;
use Oro\Bundle\MagentoBundle\Provider\Iterator\Rest\BaseMagentoRestIterator;
use Oro\Bundle\MagentoBundle\Provider\Iterator\Rest\StoresRestIterator;
use Oro\Bundle\MagentoBundle\Provider\Iterator\Rest\WebsiteRestIterator;
use Oro\Bundle\MagentoBundle\Provider\Transport\Provider\OroBridgeExtensionConfigProvider;
use Oro\Bundle\MagentoBundle\Converter\Rest\ResponseConverterManager;
use Oro\Bundle\MagentoBundle\Provider\Iterator\Rest\RegionRestIterator;
use Oro\Bundle\MagentoBundle\Utils\ValidationUtils;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class RestTransport implements
    TransportInterface,
    RestTransportInterface,
    MagentoTransportInterface,
    PingableInterface,
    LoggerAwareInterface
{
    use LoggerAwareTrait;

    const REQUIRED_EXTENSION_VERSION = '0.0.0';

    const REGION_RESPONSE_TYPE = 'region';

    const API_URL_PREFIX = 'rest/V1';
    const TOKEN_KEY = 'api_token';

    const TOKEN_HEADER_KEY  = 'Authorization';
    const TOKEN_MASK        = 'Bearer %s';

    const REST_PING_URI     = 'oro/ping';

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var MagentoTransport
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
     * @var  OroBridgeExtensionConfigProvider
     */
    protected $oroBridgeExtensionConfigProvider;

    /**
     * @var ResponseConverterManager
     */
    protected $responseConverterManager;

    /**
     * @param BridgeRestClientFactory   $clientFactory
     * @param RestTokenProvider         $restTokenProvider
     * @param OroBridgeExtensionConfigProvider $oroBridgeExtensionConfigProvider
     * @param ResponseConverterManager $responseConverterManager
     */
    public function __construct(
        BridgeRestClientFactory $clientFactory,
        RestTokenProvider $restTokenProvider,
        OroBridgeExtensionConfigProvider $oroBridgeExtensionConfigProvider,
        ResponseConverterManager $responseConverterManager
    ) {
        $this->clientFactory = $clientFactory;
        $this->restTokenProvider = $restTokenProvider;
        $this->oroBridgeExtensionConfigProvider = $oroBridgeExtensionConfigProvider;
        $this->responseConverterManager = $responseConverterManager;
    }

    /**
     * {@inheritdoc}
     */
    public function init(Transport $transportEntity)
    {
        $this->transportEntity = $transportEntity;
        $this->client = $this->clientFactory->createRestClient($this->transportEntity);
        $token = $this->restTokenProvider->getTokenFromEntity($this->transportEntity);
        $this->oroBridgeExtensionConfigProvider->setClient($this->client);

        if (null === $token) {
            $token = $this->refreshToken();
        }
        $this->updateTokenHeaderParam($token);
    }

    /**
     * @return RestClientInterface
     *
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
     * @return string
     */
    protected function refreshToken()
    {
        return $this->restTokenProvider->generateNewToken($this->transportEntity, $this->client);
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
     * {@inheritdoc}
     */
    public function isExtensionInstalled()
    {
        return !empty($this->getExtensionConfig()->getExtensionVersion());
    }

    /**
     * {@inheritdoc}
     */
    public function getAdminUrl()
    {
        return $this->getExtensionConfig()->getAdminUrl();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrders()
    {
        // TODO: Implement getOrders() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getCarts()
    {
        // TODO: Implement getCarts() method.
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getCustomerGroups()
    {
        // TODO: Implement getCustomerGroups() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getStores()
    {
        return new StoresRestIterator($this);
    }

    /**
     * {@inheritdoc}
     */
    public function doGetStoresRequest()
    {
        if (!$this->isExtensionInstalled()) {
            throw new ExtensionRequiredException();
        }

        try {
            return $this->client->get('store/storeViews', [], $this->headers)->json();
        } catch (RestException $e) {
            return $this->handleException($e, 'doGetStoresRequest');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getWebsites()
    {
        return new WebsiteRestIterator($this);
    }

    /**
     * {@inheritdoc}
     */
    public function doGetWebsitesRequest()
    {
        $this->checkExtensionInstalled();

        try {
            return $this->client->get('store/websites', [], $this->headers)->json();
        } catch (RestException $e) {
            return $this->handleException($e, 'doGetWebsitesRequest');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRegions()
    {
        return new RegionRestIterator($this, $this->transportEntity->getSettingsBag()->all());
    }

    /**
     * @return array
     */
    public function doGetRegionsRequest()
    {
        $this->checkExtensionInstalled();

        try {
            $data = $this->client->get(sprintf('directory/countries'), [], $this->headers)->json();

            return $this->responseConverterManager->convert($data, self::REGION_RESPONSE_TYPE);
        } catch (RestException $e) {
            return $this->handleException($e, 'doGetRegionsRequest');
        }
    }

    /**
     * @throws ExtensionRequiredException
     */
    protected function checkExtensionInstalled()
    {
        if (!$this->isExtensionInstalled()) {
            throw new ExtensionRequiredException();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerInfo($originId)
    {
        // TODO: Implement getCustomerInfo() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerAddresses($originId)
    {
        // TODO: Implement getCustomerAddresses() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorCode(\Exception $e)
    {
        // TODO: Implement getErrorCode() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderInfo($incrementId)
    {
        // TODO: Implement getOrderInfo() method.
    }

    /**
     * {@inheritdoc}
     */
    public function createCustomer(array $customerData)
    {
        // TODO: Implement createCustomer() method.
    }

    /**
     * {@inheritdoc}
     */
    public function updateCustomer($customerId, array $customerData)
    {
        // TODO: Implement updateCustomer() method.
    }

    /**
     * {@inheritdoc}
     */
    public function createCustomerAddress($customerId, array $item)
    {
        // TODO: Implement createCustomerAddress() method.
    }

    /**
     * {@inheritdoc}
     */
    public function updateCustomerAddress($customerAddressId, array $item)
    {
        // TODO: Implement updateCustomerAddress() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerAddressInfo($customerAddressId)
    {
        // TODO: Implement getCustomerAddressInfo() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getNewsletterSubscribers()
    {
        // TODO: Implement getNewsletterSubscribers() method.
    }

    /**
     * {@inheritdoc}
     */
    public function createNewsletterSubscriber(array $subscriberData)
    {
        // TODO: Implement createNewsletterSubscriber() method.
    }

    /**
     * {@inheritdoc}
     */
    public function updateNewsletterSubscriber($subscriberId, array $subscriberData)
    {
        // TODO: Implement updateNewsletterSubscriber() method.
    }

    /**
     * {@inheritdoc}
     */
    public function isSupportedExtensionVersion()
    {
        return $this->isExtensionInstalled()
            && version_compare($this->getExtensionVersion(), self::REQUIRED_EXTENSION_VERSION, 'ge');
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionVersion()
    {
        return $this->getExtensionConfig()->getExtensionVersion();
    }

    /**
     * {@inheritdoc}
     */
    public function getMagentoVersion()
    {
        return $this->getExtensionConfig()->getMagentoVersion();
    }

    /**
     * {@inheritdoc}
     */
    public function getServerTime()
    {
        // TODO: Implement getServerTime() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'oro.magento.transport.rest.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsFormType()
    {
        return RestTransportSettingFormType::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsEntityFQCN()
    {
        return MagentoTransport::class;
    }

    /**
     * {@inheritdoc}
     */
    public function ping()
    {
        try {
            $this->getClient()->get(static::REST_PING_URI, [], $this->headers);
        } catch (RestException $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredExtensionVersion()
    {
        return self::REQUIRED_EXTENSION_VERSION;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreditMemos()
    {
        // TODO: Implement getCreditMemos() method. Will be implemented in CRM-8310
    }

    /**
     * {@inheritdoc}
     */
    public function getCreditMemoInfo($incrementId)
    {
        // TODO: Implement getCreditMemoInfo() method. Will be implemented in CRM-8310
    }

    /**
     * Fill extension data to OroBridgeExtension object
     *
     * In case when we need refresh extension data please call this method with $force = true
     *
     * @param bool $force
     *
     * @return Config
     *
     * @throws RuntimeException
     */
    public function getExtensionConfig($force = false)
    {
        try {
            return $this->oroBridgeExtensionConfigProvider->getConfig(
                $this->headers,
                $force
            );
        } catch (RestException $e) {
            return $this->handleException($e, 'getExtensionConfig', [$force]);
        }
    }

    /**
     * @param RestException $exception
     * @param string        $methodName
     * @param array         $arguments
     *
     * @return mixed
     *
     * @throws RuntimeException
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

        /**
         * Exception caused by incorrect client settings or invalid response body
         */
        if (null === $exception->getResponse()) {
            throw new RuntimeException(
                ValidationUtils::sanitizeSecureInfo($exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }

        throw new RuntimeException(
            sprintf(
                'Server returned unexpected response. Response code %s',
                $exception->getCode()
            ),
            $exception->getCode(),
            $exception
        );
    }
}
