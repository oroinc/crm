<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Transport;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

use Oro\Bundle\SecurityBundle\Encoder\Mcrypt;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Provider\SOAPTransport as BaseSOAPTransport;

use OroCRM\Bundle\MagentoBundle\Provider\Iterator\StoresSoapIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\OrderBridgeIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\OrderSoapIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\RegionSoapIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\WebsiteSoapIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\CustomerBridgeIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\CustomerSoapIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\CustomerGroupSoapIterator;

/**
 * Magento SOAP transport
 * used to fetch and pull data to/from Magento instance
 * with sessionId param using SOAP requests
 *
 * @package OroCRM\Bundle\MagentoBundle
 */
class SoapTransport extends BaseSOAPTransport implements MagentoTransportInterface
{
    const ACTION_CUSTOMER_LIST = 'customerCustomerList';
    const ACTION_CUSTOMER_INFO = 'customerCustomerInfo';
    const ACTION_ADDRESS_LIST  = 'customerAddressList';
    const ACTION_GROUP_LIST    = 'customerGroupList';
    const ACTION_STORE_LIST    = 'storeList';
    const ACTION_ORDER_LIST    = 'salesOrderList';
    const ACTION_ORDER_INFO    = 'salesOrderInfo';
    const ACTION_CART_LIST     = 'salesQuoteList';
    const ACTION_CART_INFO     = 'shoppingCartInfo';
    const ACTION_PING          = 'oroPing';
    const ACTION_COUNTRY_LIST  = 'directoryCountryList';
    const ACTION_REGION_LIST   = 'directoryRegionList';

    /** @var string */
    protected $sessionId;

    /** @var Mcrypt */
    protected $encoder;

    /** @var bool */
    protected $isExtensionInstalled;

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

        $apiUser = $this->settings->get('api_user', false);
        $apiKey  = $this->settings->get('api_key', false);
        $apiKey  = $this->encoder->decryptData($apiKey);

        if (!$apiUser || !$apiKey) {
            throw new InvalidConfigurationException(
                "Magento SOAP transport require 'api_key' and 'api_user' settings to be defined."
            );
        }

        /** @var string sessionId returned by Magento API login method */
        $this->sessionId = $this->client->login($apiUser, $apiKey);
    }

    /**
     * {@inheritdoc}
     */
    public function call($action, array $params = [])
    {
        return parent::call($action, array_merge([$this->sessionId], $params));
    }

    /**
     * {@inheritdoc}
     */
    public function isExtensionInstalled()
    {
        if (null === $this->isExtensionInstalled) {
            try {
                $isExtensionInstalled       = $this->call(self::ACTION_PING);
                $this->isExtensionInstalled = !empty($isExtensionInstalled->version);
            } catch (\Exception $e) {
                $this->isExtensionInstalled = false;
            }
        }

        return $this->isExtensionInstalled;
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
}
