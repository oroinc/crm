<?php

namespace Oro\Bundle\MagentoBundle\Provider\Transport;

use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Provider\PingableInterface;
use Oro\Bundle\IntegrationBundle\Provider\SOAPTransport as BaseSOAPTransport;
use Oro\Bundle\IntegrationBundle\Provider\TransportCacheClearInterface;
use Oro\Bundle\IntegrationBundle\Utils\ConverterUtils;
use Oro\Bundle\IntegrationBundle\Utils\MultiAttemptsConfigTrait;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use Oro\Bundle\MagentoBundle\Exception\ExtensionRequiredException;
use Oro\Bundle\MagentoBundle\Form\Type\SoapTransportSettingFormType;
use Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\CartsBridgeIterator;
use Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\CreditMemoSoapIterator;
use Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\CustomerBridgeIterator;
use Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\CustomerGroupSoapIterator;
use Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\CustomerSoapIterator;
use Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\NewsletterSubscriberBridgeIterator;
use Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\OrderBridgeIterator;
use Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\OrderSoapIterator;
use Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\RegionBridgeIterator;
use Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\RegionSoapIterator;
use Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\StoresSoapIterator;
use Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\WebsiteSoapIterator;
use Oro\Bundle\MagentoBundle\Provider\UniqueCustomerEmailSoapProvider;
use Oro\Bundle\MagentoBundle\Service\WsdlManager;
use Oro\Bundle\MagentoBundle\Utils\WSIUtils;
use Oro\Bundle\SecurityBundle\Encoder\SymmetricCrypterInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * Magento SOAP transport
 * used to fetch and pull data to/from Magento instance
 * with sessionId param using SOAP requests
 *
 * @package Oro\Bundle\MagentoBundle
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class SoapTransport extends BaseSOAPTransport implements
    MagentoSoapTransportInterface,
    PingableInterface,
    TransportCacheClearInterface
{
    use ExtensionVersionTrait;
    use MultiAttemptsConfigTrait;

    const REQUIRED_EXTENSION_VERSION = '1.2.0';

    const ORDER_NOTE_VERSION_REQUIRED = '1.2.19';

    const ACTION_ORO_REGION_LIST_VERSION_REQUIRED = '1.2.14';

    const ACTION_CUSTOMER_LIST = 'customerCustomerList';
    const ACTION_CUSTOMER_INFO = 'customerCustomerInfo';
    const ACTION_CUSTOMER_UPDATE = 'customerCustomerUpdate';
    const ACTION_CUSTOMER_DELETE = 'customerCustomerDelete';
    const ACTION_CUSTOMER_CREATE = 'customerCustomerCreate';
    const ACTION_CUSTOMER_ADDRESS_LIST = 'customerAddressList';
    const ACTION_CUSTOMER_ADDRESS_INFO = 'customerAddressInfo';
    const ACTION_CUSTOMER_ADDRESS_UPDATE = 'customerAddressUpdate';
    const ACTION_CUSTOMER_ADDRESS_DELETE = 'customerAddressDelete';
    const ACTION_CUSTOMER_ADDRESS_CREATE = 'customerAddressCreate';
    const ACTION_ADDRESS_LIST = 'customerAddressList';
    const ACTION_GROUP_LIST = 'customerGroupList';
    const ACTION_STORE_LIST = 'storeList';
    const ACTION_ORDER_LIST = 'salesOrderList';
    const ACTION_ORDER_INFO = 'salesOrderInfo';
    const ACTION_CREDIT_MEMO_LIST = 'salesOrderCreditmemoList';
    const ACTION_CREDIT_MEMO_INFO = 'salesOrderCreditmemoInfo';
    const ACTION_CART_INFO = 'shoppingCartInfo';
    const ACTION_COUNTRY_LIST = 'directoryCountryList';
    const ACTION_REGION_LIST = 'directoryRegionList';
    const ACTION_PING = 'oroPing';

    const ACTION_ORO_CART_LIST = 'oroQuoteList';
    const ACTION_ORO_ORDER_LIST = 'oroOrderList';
    const ACTION_ORO_ORDER_INFO = 'oroOrderInfo';
    const ACTION_ORO_CUSTOMER_LIST = 'oroCustomerList';
    const ACTION_ORO_CUSTOMER_INFO = 'oroCustomerInfo';
    const ACTION_ORO_CUSTOMER_ADDRESS_LIST = 'oroCustomerAddressList';
    const ACTION_ORO_CUSTOMER_ADDRESS_INFO = 'oroCustomerAddressInfo';
    const ACTION_ORO_CUSTOMER_CREATE = 'oroCustomerCreate';
    const ACTION_ORO_CUSTOMER_UPDATE = 'oroCustomerUpdate';
    const ACTION_ORO_NEWSLETTER_SUBSCRIBER_LIST = 'newsletterSubscriberList';
    const ACTION_ORO_NEWSLETTER_SUBSCRIBER_CREATE = 'newsletterSubscriberCreate';
    const ACTION_ORO_NEWSLETTER_SUBSCRIBER_UPDATE = 'newsletterSubscriberUpdate';
    const ACTION_ORO_WEBSITE_LIST = 'oroWebsiteList';
    const ACTION_ORO_REGION_LIST = 'oroRegionList';

    const SOAP_FAULT_ADDRESS_DOES_NOT_EXIST = 102;

    /** @var string */
    protected $sessionId;

    /** @var SymmetricCrypterInterface */
    protected $encoder;

    /** @var bool */
    protected $isExtensionInstalled;

    /** @var string */
    protected $magentoVersion;

    /** @var string */
    protected $extensionVersion;

    /** @var bool */
    protected $isWsiMode = false;

    /** @var string */
    protected $adminUrl;

    /** @var  string */
    protected $serverTime;

    /** @var array */
    protected $dependencies = [];

    /** @var WsdlManager */
    protected $wsdlManager;

    /** @var array */
    protected $auth = [];

    /**
     * @var array
     */
    protected $bundleConfig;

    /**
     * @var UniqueCustomerEmailSoapProvider
     */
    protected $uniqueCustomerEmailProvider;

    /**
     * @var array
     */
    private $clientAdditionalParams = [];

    /**
     * @param SymmetricCrypterInterface       $encoder
     * @param WsdlManager                     $wsdlManager
     * @param UniqueCustomerEmailSoapProvider $uniqueCustomerEmailProvider
     * @param array                           $bundleConfig
     */
    public function __construct(
        SymmetricCrypterInterface $encoder,
        WsdlManager $wsdlManager,
        UniqueCustomerEmailSoapProvider $uniqueCustomerEmailProvider,
        array $bundleConfig = []
    ) {
        $this->encoder = $encoder;
        $this->wsdlManager = $wsdlManager;
        $this->uniqueCustomerEmailProvider = $uniqueCustomerEmailProvider;
        $this->bundleConfig = $bundleConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function init(Transport $transportEntity)
    {
        /**
         * Cache WSDL and force transport entity to use it instead of original URL.
         * This should be done before parent::init as settings will be cached there.
         */
        if ($transportEntity instanceof MagentoSoapTransport) {
            $wsdlUrl = $transportEntity->getApiUrl();

            // Save auth information to be able to perform requests.
            $urlParts = parse_url($wsdlUrl);
            if (isset($urlParts['user'], $urlParts['pass'])) {
                $this->auth['login'] = $urlParts['user'];
                $this->auth['password'] = $urlParts['pass'];
            }

            // Load WSDL to local cache.
            if (!$this->wsdlManager->isCacheLoaded($wsdlUrl)) {
                $this->wsdlManager->loadWsdl($wsdlUrl);
            }

            // Set cached WSDL path to transport entity.
            $transportEntity->setWsdlCachePath($this->wsdlManager->getCachedWsdlPath($wsdlUrl));
        }

        parent::init($transportEntity);
        $this->processClientAdditionalParameters($this->clientAdditionalParams);

        $wsiMode = $this->settings->get('wsi_mode', false);
        $apiUser = $this->settings->get('api_user', false);
        $apiKey = $this->settings->get('api_key', false);
        $apiKey = $this->encoder->decryptData($apiKey);

        if (!$apiUser || !$apiKey) {
            throw new InvalidConfigurationException(
                "Magento SOAP transport require 'api_key' and 'api_user' settings to be defined."
            );
        }

        // revert initial state
        $this->adminUrl = null;
        $this->isWsiMode = $wsiMode;
        $this->serverTime = null;

        // Clear session id if init() method is called several times. Look to the call() method.
        $this->sessionId = null;
        /** @var string sessionId returned by Magento API login method */
        $this->sessionId = $this->call('login', ['username' => $apiUser, 'apiKey' => $apiKey]);

        $this->checkExtensionFunctions();
    }

    /**
     * {@inheritdoc}
     */
    public function initWithExtraOptions(Transport $transportEntity, array $clientExtraOptions)
    {
        $this->clientAdditionalParams = $clientExtraOptions;
        $this->init($transportEntity);
    }

    /**
     * {@inheritdoc}
     */
    public function resetInitialState()
    {
        $this->isExtensionInstalled = null;
    }

    /**
     * @param array $clientAdditionalParams
     */
    protected function processClientAdditionalParameters(array $clientAdditionalParams)
    {
        $configuration = $this->multiAttemptsDefaultConfigurationParameters;
        if (isset($clientAdditionalParams[self::$multiAttemptsConfigKey])) {
            $configuration = array_merge($configuration, $clientAdditionalParams[self::$multiAttemptsConfigKey]);
        }

        $this->setMultipleAttemptsEnabled(
            $this->getMultiAttemptsEnabledParameter($configuration)
        );
        $this->setSleepBetweenAttempt(
            $this->getSleepBetweenAttemptsParameter($configuration)
        );
    }

    protected function checkExtensionFunctions()
    {
        $functions = (array)$this->client->__getFunctions();

        $isExtensionInstalled = (bool)array_filter(
            $functions,
            function ($definition) {
                return false !== strpos($definition, self::ACTION_PING);
            }
        );

        if (!$isExtensionInstalled) {
            $this->isExtensionInstalled = false;
            $this->adminUrl = false;
        }
    }

    /**
     * Disable wsdl caching by PHP.
     *
     * {@inheritdoc}
     */
    protected function getSoapClient($wsdlUrl, array $options = [])
    {
        $options['cache_wsdl'] = WSDL_CACHE_NONE;
        if (!isset($options['login'], $options['password'])) {
            $options = array_merge($options, $this->auth);
        }
        if (!empty($this->bundleConfig['sync_settings']['skip_ssl_verification'])) {
            $context = stream_context_create(
                [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ]
                ]
            );

            $options['stream_context'] = $context;
        }

        return parent::getSoapClient($wsdlUrl, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function call($action, $params = [])
    {
        if (null !== $this->sessionId) {
            $params = array_merge(['sessionId' => $this->sessionId], (array)$params);
        }

        if ($this->logger) {
            $this->logger->debug(
                sprintf(
                    '[%.1fMB/%.1fMB] Call %s action with %s parameters',
                    memory_get_usage() / 1024 / 1024,
                    memory_get_peak_usage() / 1024 / 1024,
                    $action,
                    json_encode($params)
                )
            );
        }

        if ($this->isWsiMode) {
            $result = parent::call($action, [(object)$params]);
            $result = WSIUtils::parseWSIResponse($result);
        } else {
            $result = parent::call($action, $params);
        }

        $this->lookUpForServerTime();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function makeNewAttempt($action, $params)
    {
        $this->logAttempt();
        sleep($this->getSleepBetweenAttempt());
        ++$this->attempted;

        // in case if we have WsiMode enabled we should convert object parameters to array to avoid
        // not correct parameters during the next attempt call
        return $this->call(
            $action,
            $this->isWsiMode ? (array)array_shift($params) : $params
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isExtensionInstalled()
    {
        if (null === $this->isExtensionInstalled) {
            $this->pingMagento();
        }

        return $this->isExtensionInstalled;
    }

    /**
     * {@inheritdoc}
     */
    public function getMagentoVersion()
    {
        if (null === $this->isExtensionInstalled) {
            $this->pingMagento();
        }

        return $this->magentoVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionVersion()
    {
        if (null === $this->isExtensionInstalled) {
            $this->pingMagento();
        }

        return $this->extensionVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function isSupportedExtensionVersion()
    {
        return $this->isSupportedVersion($this->getExtensionVersion());
    }

    /**
     * {@inheritdoc}
     */
    public function isSupportedOrderNoteExtensionVersion()
    {
        return $this->isExtensionInstalledAndIsVersionSupported(
            self::ORDER_NOTE_VERSION_REQUIRED
        );
    }

    /**
     * {@inheritdoc}
     */
    public function ping()
    {
        return $this->isExtensionInstalled();
    }

    /**
     * @param string|null $versionRequired
     * @return bool
     */
    public function isExtensionInstalledAndIsVersionSupported($versionRequired = null)
    {
        $versionRequired = $versionRequired === null ? self::REQUIRED_EXTENSION_VERSION : $versionRequired;

        return $this->isExtensionInstalled()
            && version_compare($this->getExtensionVersion(), $versionRequired, 'ge');
    }

    /**
     * Pings magento and fill data related to Bridge Extension.
     *
     * @return $this
     */
    protected function pingMagento()
    {
        if (null === $this->isExtensionInstalled && null === $this->adminUrl) {
            try {
                $result = $this->call(self::ACTION_PING);
                $this->isExtensionInstalled = !empty($result->version);
                if ($this->isExtensionInstalled) {
                    $this->extensionVersion = $result->version;
                }
                if (!empty($result->mage_version)) {
                    $this->magentoVersion = $result->mage_version;
                }
                if (!empty($result->admin_url)) {
                    $this->adminUrl = $result->admin_url;
                } else {
                    $this->adminUrl = false;
                }
            } catch (\Exception $e) {
                $this->isExtensionInstalled
                    = $this->adminUrl
                    = false;
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdminUrl()
    {
        if (null === $this->adminUrl) {
            $this->pingMagento();
        }

        return $this->adminUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getServerTime()
    {
        return $this->serverTime;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrders()
    {
        $settings = $this->settings->all();

        if ($this->isExtensionInstalled()) {
            return new OrderBridgeIterator($this, $settings);
        } else {
            return new OrderSoapIterator($this, $settings);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderInfo($incrementId)
    {
        if ($this->isSupportedExtensionVersion()) {
            $endpoint = self::ACTION_ORO_ORDER_INFO;
        } else {
            $endpoint = self::ACTION_ORDER_INFO;
        }

        return $this->call($endpoint, ['orderIncrementId' => $incrementId]);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreditMemos()
    {
        $settings = $this->settings->all();

        return new CreditMemoSoapIterator($this, $settings);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreditMemoInfo($incrementId)
    {
        return $this->call(self::ACTION_CREDIT_MEMO_INFO, ['creditmemoIncrementId' => $incrementId]);
    }

    /**
     * {@inheritdoc}
     */
    public function getCarts()
    {
        if ($this->isExtensionInstalled()) {
            return new CartsBridgeIterator($this, $this->settings->all());
        }

        throw new ExtensionRequiredException();
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomers()
    {
        $settings = $this->settings->all();

        if ($this->isExtensionInstalled()) {
            return new CustomerBridgeIterator($this, $settings);
        } else {
            return new CustomerSoapIterator($this, $settings);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isCustomerHasUniqueEmail(Customer $customer)
    {
        return $this->uniqueCustomerEmailProvider->isCustomerHasUniqueEmail($this, $customer);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerGroups()
    {
        return new CustomerGroupSoapIterator($this);
    }

    /**
     * {@inheritdoc}
     */
    public function getStores()
    {
        return new StoresSoapIterator($this);
    }

    /**
     * {@inheritdoc}
     */
    public function getWebsites()
    {
        return new WebsiteSoapIterator($this);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegions()
    {
        $settings = $this->settings->all();

        if ($this->isExtensionInstalledAndIsVersionSupported(self::ACTION_ORO_REGION_LIST_VERSION_REQUIRED)) {
            return new RegionBridgeIterator($this, $settings);
        } else {
            return new RegionSoapIterator($this, $settings);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerAddresses($originId)
    {
        if ($this->isSupportedExtensionVersion()) {
            $endpoint = SoapTransport::ACTION_ORO_CUSTOMER_ADDRESS_LIST;
        } else {
            $endpoint = SoapTransport::ACTION_CUSTOMER_ADDRESS_LIST;
        }

        $addresses = $this->call($endpoint, ['customerId' => $originId]);
        $addresses = WSIUtils::processCollectionResponse($addresses);

        return ConverterUtils::objectToArray($addresses);
    }

    /**
     * {@inheritdoc}
     */
    public function createCustomer(array $customerData)
    {
        if ($this->isSupportedExtensionVersion()) {
            $createEndpoint = SoapTransport::ACTION_ORO_CUSTOMER_CREATE;
        } else {
            $createEndpoint = SoapTransport::ACTION_CUSTOMER_CREATE;
        }

        return $this->call($createEndpoint, ['customerData' => $customerData]);
    }

    /**
     * {@inheritdoc}
     */
    public function updateCustomer($customerId, array $customerData)
    {
        if ($this->isSupportedExtensionVersion()) {
            $updateEndpoint = SoapTransport::ACTION_ORO_CUSTOMER_UPDATE;
        } else {
            $updateEndpoint = SoapTransport::ACTION_CUSTOMER_UPDATE;
        }

        return $this->call($updateEndpoint, ['customerId' => $customerId, 'customerData' => $customerData]);
    }

    /**
     * {@inheritdoc}
     */
    public function createCustomerAddress($customerId, array $item)
    {
        return $this->call(
            SoapTransport::ACTION_CUSTOMER_ADDRESS_CREATE,
            ['customerId' => $customerId, 'addressData' => $item]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function updateCustomerAddress($customerAddressId, array $item)
    {
        return $this->call(
            SoapTransport::ACTION_CUSTOMER_ADDRESS_UPDATE,
            ['addressId' => $customerAddressId, 'addressData' => $item]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerAddressInfo($customerAddressId)
    {
        if ($this->isSupportedExtensionVersion()) {
            $endpoint = SoapTransport::ACTION_ORO_CUSTOMER_ADDRESS_INFO;
        } else {
            $endpoint = SoapTransport::ACTION_CUSTOMER_ADDRESS_INFO;
        }

        return ConverterUtils::objectToArray($this->call($endpoint, ['addressId' => $customerAddressId]));
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerInfo($originId)
    {
        if ($this->isSupportedExtensionVersion()) {
            $endpoint = SoapTransport::ACTION_ORO_CUSTOMER_INFO;
        } else {
            $endpoint = SoapTransport::ACTION_CUSTOMER_INFO;
        }

        return ConverterUtils::objectToArray($this->call($endpoint, ['customerId' => $originId]));
    }

    /**
     * {@inheritdoc}
     */
    public function getNewsletterSubscribers()
    {
        if ($this->isSupportedExtensionVersion()) {
            return new NewsletterSubscriberBridgeIterator($this, $this->settings->all());
        }

        throw new ExtensionRequiredException();
    }

    /**
     * {@inheritdoc}
     */
    public function createNewsletterSubscriber(array $subscriberData)
    {
        if ($this->isExtensionInstalled()) {
            $result = $this->call(
                SoapTransport::ACTION_ORO_NEWSLETTER_SUBSCRIBER_CREATE,
                ['subscriberData' => $subscriberData]
            );

            return ConverterUtils::objectToArray($result);
        }

        throw new ExtensionRequiredException();
    }

    /**
     * {@inheritdoc}
     */
    public function updateNewsletterSubscriber($subscriberId, array $subscriberData)
    {
        if ($this->isExtensionInstalled()) {
            $result = $this->call(
                SoapTransport::ACTION_ORO_NEWSLETTER_SUBSCRIBER_UPDATE,
                ['subscriberId' => $subscriberId, 'subscriberData' => $subscriberData]
            );

            return ConverterUtils::objectToArray($result);
        }

        throw new ExtensionRequiredException();
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorCode(\Exception $e)
    {
        if ($e instanceof \SoapFault) {
            switch ($e->faultcode) {
                case self::SOAP_FAULT_ADDRESS_DOES_NOT_EXIST:
                    return self::TRANSPORT_ERROR_ADDRESS_DOES_NOT_EXIST;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'oro.magento.transport.soap.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsFormType()
    {
        return SoapTransportSettingFormType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsEntityFQCN()
    {
        return MagentoSoapTransport::class;
    }

    /**
     * Tries to fetch date from response headers
     */
    protected function lookUpForServerTime()
    {
        if (null === $this->serverTime) {
            $parsedResponse = $this->getLastResponseHeaders();

            if (isset($parsedResponse['headers']['Date'])) {
                $this->serverTime = $parsedResponse['headers']['Date'];
            } else {
                $this->serverTime = false;
            }
        }
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
    public function getOrderNoteRequiredExtensionVersion()
    {
        return self::ORDER_NOTE_VERSION_REQUIRED;
    }

    /**
     * {@inheritdoc}
     */
    public function cacheClear($url = null)
    {
        $this->wsdlManager->clearCacheForUrl($url);
    }
}
