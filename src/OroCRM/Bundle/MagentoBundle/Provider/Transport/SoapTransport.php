<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Transport;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Provider\SOAPTransport as BaseSOAPTransport;
use Oro\Bundle\SecurityBundle\Encoder\Mcrypt;

use OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use OroCRM\Bundle\MagentoBundle\Exception\ExtensionRequiredException;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\CartsBridgeIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\CustomerBridgeIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\CustomerGroupSoapIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\CustomerSoapIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\NewsletterSubscriberBridgeIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\OrderBridgeIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\OrderSoapIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\RegionSoapIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\StoresSoapIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\WebsiteSoapIterator;
use OroCRM\Bundle\MagentoBundle\Service\WsdlManager;
use OroCRM\Bundle\MagentoBundle\Utils\WSIUtils;

/**
 * Magento SOAP transport
 * used to fetch and pull data to/from Magento instance
 * with sessionId param using SOAP requests
 *
 * @package OroCRM\Bundle\MagentoBundle
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class SoapTransport extends BaseSOAPTransport implements MagentoTransportInterface, ServerTimeAwareInterface
{
    const REQUIRED_EXTENSION_VERSION = '1.2.0';

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
    const ACTION_CART_INFO = 'shoppingCartInfo';
    const ACTION_COUNTRY_LIST = 'directoryCountryList';
    const ACTION_REGION_LIST = 'directoryRegionList';
    const ACTION_PING = 'oroPing';

    const ACTION_ORO_CART_LIST = 'oroQuoteList';
    const ACTION_ORO_ORDER_LIST = 'oroOrderList';
    const ACTION_ORO_CUSTOMER_LIST = 'oroCustomerList';
    const ACTION_ORO_CUSTOMER_UPDATE = 'oroCustomerUpdate';
    const ACTION_ORO_NEWSLETTER_SUBSCRIBER_LIST = 'newsletterSubscriberList';
    const ACTION_ORO_NEWSLETTER_SUBSCRIBER_CREATE = 'newsletterSubscriberCreate';
    const ACTION_ORO_NEWSLETTER_SUBSCRIBER_UPDATE = 'newsletterSubscriberUpdate';

    const SOAP_FAULT_ADDRESS_DOES_NOT_EXIST = 102;

    /** @var string */
    protected $sessionId;

    /** @var Mcrypt */
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

    /**
     * @param Mcrypt $encoder
     * @param WsdlManager $wsdlManager
     */
    public function __construct(Mcrypt $encoder, WsdlManager $wsdlManager)
    {
        $this->encoder = $encoder;
        $this->wsdlManager = $wsdlManager;
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
            $wsdlUrl = $transportEntity->getWsdlUrl();
            if (!$this->wsdlManager->isCacheLoaded($wsdlUrl)) {
                $this->wsdlManager->loadWsdl($wsdlUrl);
            }

            $transportEntity->setWsdlCachePath($this->wsdlManager->getCachedWsdlPath($wsdlUrl));
        }

        parent::init($transportEntity);

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
        $this->isExtensionInstalled = null;
        $this->isWsiMode = $wsiMode;

        /** @var string sessionId returned by Magento API login method */
        $this->sessionId = null;
        $this->sessionId = $this->call('login', ['username' => $apiUser, 'apiKey' => $apiKey]);
    }

    /**
     * Disable wsdl caching by PHP.
     *
     * {@inheritdoc}
     */
    protected function getSoapClient($wsdlUrl, array $options = [])
    {
        $options['cache_wsdl'] = WSDL_CACHE_NONE;

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
            $this->logger->debug(sprintf('Call %s action with %s parameters', $action, json_encode($params)));
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
        return $this->isExtensionInstalled()
            && version_compare($this->getExtensionVersion(), self::REQUIRED_EXTENSION_VERSION, 'ge');
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

        if ($this->isSupportedExtensionVersion()) {
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
        return $this->call(self::ACTION_ORDER_INFO, ['orderIncrementId' => $incrementId]);
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(array $dependenciesToLoad = null, $force = false)
    {
        if ($force && null === $dependenciesToLoad) {
            $dependenciesToLoad = array_keys($this->dependencies);
        }

        $dependencies = [];
        foreach ($dependenciesToLoad as $dependencyToLoad) {
            switch ($dependencyToLoad) {
                case MagentoTransportInterface::ALIAS_STORES:
                    $dependencies[$dependencyToLoad] = $this->getStoreDependency($force);
                    break;
                case MagentoTransportInterface::ALIAS_WEBSITES:
                    $dependencies[$dependencyToLoad] = $this->getWebsiteDependency($force);
                    break;
                case MagentoTransportInterface::ALIAS_GROUPS:
                    $dependencies[$dependencyToLoad] = $this->getCustomerGroupsDependency($force);
                    break;
            }
        }

        return $dependencies;
    }

    /**
     * @param bool $force
     * @return array
     */
    protected function getStoreDependency($force = false)
    {
        if ($force || !array_key_exists(MagentoTransportInterface::ALIAS_STORES, $this->dependencies)) {
            $this->dependencies[MagentoTransportInterface::ALIAS_STORES] = iterator_to_array($this->getStores());
        }

        return $this->dependencies[MagentoTransportInterface::ALIAS_STORES];
    }

    /**
     * @param bool $force
     * @return array
     */
    protected function getWebsiteDependency($force = false)
    {
        if ($force || !array_key_exists(MagentoTransportInterface::ALIAS_WEBSITES, $this->dependencies)) {
            $this->dependencies[MagentoTransportInterface::ALIAS_WEBSITES] = iterator_to_array($this->getWebsites());
        }

        return $this->dependencies[MagentoTransportInterface::ALIAS_WEBSITES];
    }

    /**
     * @param bool $force
     * @return array
     */
    protected function getCustomerGroupsDependency($force = false)
    {
        if ($force || !array_key_exists(MagentoTransportInterface::ALIAS_GROUPS, $this->dependencies)) {
            $this->dependencies[MagentoTransportInterface::ALIAS_GROUPS]
                = iterator_to_array($this->getCustomerGroups());
        }

        return $this->dependencies[MagentoTransportInterface::ALIAS_GROUPS];
    }

    /**
     * {@inheritdoc}
     */
    public function getCarts()
    {
        if ($this->isSupportedExtensionVersion()) {
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

        if ($this->isSupportedExtensionVersion()) {
            return new CustomerBridgeIterator($this, $settings);
        } else {
            return new CustomerSoapIterator($this, $settings);
        }
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
        return new RegionSoapIterator($this, $this->settings->all());
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerAddresses($originId)
    {
        $addresses = $this->call(SoapTransport::ACTION_CUSTOMER_ADDRESS_LIST, ['customerId' => $originId]);
        $addresses = WSIUtils::processCollectionResponse($addresses);

        return $addresses;
    }

    /**
     * {@inheritdoc}
     */
    public function createCustomer(array $customerData)
    {
        return $this->call(SoapTransport::ACTION_CUSTOMER_CREATE, ['customerData' => $customerData]);
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
        return (array)$this->call(SoapTransport::ACTION_CUSTOMER_ADDRESS_INFO, ['addressId' => $customerAddressId]);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerInfo($originId)
    {
        return (array)$this->call(SoapTransport::ACTION_CUSTOMER_INFO, ['customerId' => $originId]);
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
            return (array)$this->call(
                SoapTransport::ACTION_ORO_NEWSLETTER_SUBSCRIBER_CREATE,
                ['subscriberData' => $subscriberData]
            );
        }

        throw new ExtensionRequiredException();
    }

    /**
     * {@inheritdoc}
     */
    public function updateNewsletterSubscriber($subscriberId, array $subscriberData)
    {
        if ($this->isExtensionInstalled()) {
            return (array)$this->call(
                SoapTransport::ACTION_ORO_NEWSLETTER_SUBSCRIBER_UPDATE,
                ['subscriberId' => $subscriberId, 'subscriberData' => $subscriberData]
            );
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
        return 'orocrm.magento.transport.soap.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsFormType()
    {
        return 'orocrm_magento_soap_transport_setting_form_type';
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsEntityFQCN()
    {
        return 'OroCRM\\Bundle\\MagentoBundle\\Entity\\MagentoSoapTransport';
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
}
