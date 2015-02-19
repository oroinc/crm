<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Transport;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

use Oro\Bundle\SecurityBundle\Encoder\Mcrypt;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Provider\SOAPTransport as BaseSOAPTransport;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Utils\WSIUtils;
use OroCRM\Bundle\MagentoBundle\Exception\ExtensionRequiredException;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\CartsBridgeIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\OrderBridgeIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\CustomerBridgeIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\OrderSoapIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\RegionSoapIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\StoresSoapIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\WebsiteSoapIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\CustomerSoapIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\CustomerGroupSoapIterator;

/**
 * Magento SOAP transport
 * used to fetch and pull data to/from Magento instance
 * with sessionId param using SOAP requests
 *
 * @package OroCRM\Bundle\MagentoBundle
 */
class SoapTransport extends BaseSOAPTransport implements MagentoTransportInterface, ServerTimeAwareInterface
{
    const ACTION_CUSTOMER_LIST           = 'customerCustomerList';
    const ACTION_CUSTOMER_INFO           = 'customerCustomerInfo';
    const ACTION_CUSTOMER_UPDATE         = 'customerCustomerUpdate';
    const ACTION_CUSTOMER_DELETE         = 'customerCustomerDelete';
    const ACTION_CUSTOMER_ADDRESS_LIST   = 'customerAddressList';
    const ACTION_CUSTOMER_ADDRESS_INFO   = 'customerAddressInfo';
    const ACTION_CUSTOMER_ADDRESS_UPDATE = 'customerAddressUpdate';
    const ACTION_CUSTOMER_ADDRESS_DELETE = 'customerAddressDelete';
    const ACTION_CUSTOMER_ADDRESS_CREATE = 'customerAddressCreate';
    const ACTION_ADDRESS_LIST            = 'customerAddressList';
    const ACTION_GROUP_LIST              = 'customerGroupList';
    const ACTION_STORE_LIST              = 'storeList';
    const ACTION_ORDER_LIST              = 'salesOrderList';
    const ACTION_ORDER_INFO              = 'salesOrderInfo';
    const ACTION_CART_INFO               = 'shoppingCartInfo';
    const ACTION_COUNTRY_LIST            = 'directoryCountryList';
    const ACTION_REGION_LIST             = 'directoryRegionList';
    const ACTION_PING                    = 'oroPing';

    const ACTION_ORO_CART_LIST     = 'oroQuoteList';
    const ACTION_ORO_ORDER_LIST    = 'oroOrderList';
    const ACTION_ORO_CUSTOMER_LIST = 'oroCustomerList';

    const SOAP_FAULT_ADDRESS_DOES_NOT_EXIST = 102;

    /** @var string */
    protected $sessionId;

    /** @var Mcrypt */
    protected $encoder;

    /** @var bool */
    protected $isExtensionInstalled;

    /** @var bool */
    protected $isWsiMode = false;

    /** @var string */
    protected $adminUrl;

    /** @var  string */
    protected $serverTime;

    public function __construct(Mcrypt $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * {@inheritdoc}
     */
    public function init(Transport $transportEntity)
    {
        parent::init($transportEntity);

        $wsiMode = $this->settings->get('wsi_mode', false);
        $apiUser = $this->settings->get('api_user', false);
        $apiKey  = $this->settings->get('api_key', false);
        $apiKey  = $this->encoder->decryptData($apiKey);

        if (!$apiUser || !$apiKey) {
            throw new InvalidConfigurationException(
                "Magento SOAP transport require 'api_key' and 'api_user' settings to be defined."
            );
        }

        // revert initial state
        $this->isExtensionInstalled = null;
        $this->isWsiMode            = $wsiMode;

        /** @var string sessionId returned by Magento API login method */
        $this->sessionId = $this->call('login', ['username' => $apiUser, 'apiKey' => $apiKey]);
    }

    /**
     * {@inheritdoc}
     */
    public function call($action, $params = [])
    {
        if (null !== $this->sessionId) {
            $params = array_merge(['sessionId' => $this->sessionId], (array)$params);
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
     * Pings magento and fill $isExtensionInstalled and $adminUrl
     *
     * @return $this
     */
    protected function pingMagento()
    {
        if (null === $this->isExtensionInstalled && null === $this->adminUrl) {
            try {
                $result                     = $this->call(self::ACTION_PING);
                $this->isExtensionInstalled = !empty($result->version);
                $this->adminUrl             = !empty($result->admin_url) ? $result->admin_url : false;
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
    public function getCustomerAddresses(Customer $customer)
    {
        $customerId = $customer->getOriginId();
        $addresses  = $this->call(SoapTransport::ACTION_CUSTOMER_ADDRESS_LIST, ['customerId' => $customerId]);
        $addresses  = WSIUtils::processCollectionResponse($addresses);

        return $addresses;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorCode(\Exception $e)
    {
        if ($e instanceof \SoapFault) {
            switch($e->faultcode) {
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

            $this->serverTime = isset($parsedResponse['headers'], $parsedResponse['headers']['Date'])
                ? $parsedResponse['headers']['Date'] : false;
        }
    }
}
