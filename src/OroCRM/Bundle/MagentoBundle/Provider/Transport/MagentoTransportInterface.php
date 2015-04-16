<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Transport;

use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;

use OroCRM\Bundle\MagentoBundle\Provider\Iterator\UpdatedLoaderInterface;

interface MagentoTransportInterface extends TransportInterface
{
    const ALIAS_GROUPS = 'groups';
    const ALIAS_STORES = 'stores';
    const ALIAS_WEBSITES = 'websites';
    const ALIAS_REGIONS = 'regions';

    const WEBSITE_CODE_SEPARATOR = ' / ';
    const WEBSITE_NAME_SEPARATOR = ', ';

    const TRANSPORT_ERROR_ADDRESS_DOES_NOT_EXIST = 102;

    /**
     * @param string       $action
     * @param object|array $params
     *
     * @return mixed
     */
    public function call($action, $params = []);

    /**
     * Return true if oro bridge extension installed on remote instance
     *
     * @return bool
     */
    public function isExtensionInstalled();

    /**
     * Return string if oro bridge extension installed on remote instance
     *
     * @return mixed
     */
    public function getAdminUrl();

    /**
     * Retrieve orders from magento
     *
     * @return UpdatedLoaderInterface|\Iterator
     */
    public function getOrders();

    /**
     * Retrieve shopping carts list from magento
     *
     * @return \Iterator
     */
    public function getCarts();

    /**
     * Retrieve customers from magento
     *
     * @return UpdatedLoaderInterface|\Iterator
     */
    public function getCustomers();

    /**
     * Retrieve customer groups list from magento
     *
     * @return \Iterator
     */
    public function getCustomerGroups();

    /**
     * Retrieve store list from magento
     *
     * @return \Iterator
     */
    public function getStores();

    /**
     * Retrieve website list from magento
     *
     * @return \Iterator
     */
    public function getWebsites();

    /**
     * Retrieve regions list from magento
     *
     * @return \Iterator
     */
    public function getRegions();

    /**
     * Retrieve customer information from magento.
     *
     * @param string $originId
     * @return array
     */
    public function getCustomerInfo($originId);

    /**
     * Retrieve customer address list
     *
     * @param string $originId
     *
     * @return array
     */
    public function getCustomerAddresses($originId);

    /**
     * Parse exception from remote side and returns generic code
     *
     * @param \Exception $e
     *
     * @return int
     */
    public function getErrorCode(\Exception $e);

    /**
     * @param string $incrementId
     */
    public function getOrderInfo($incrementId);

    /**
     * @param array $customerData
     *
     * @return int ID of the created customer
     */
    public function createCustomer(array $customerData);

    /**
     * @param int $customerId
     * @param array $customerData
     *
     * @return bool True if the customer is updated
     */
    public function updateCustomer($customerId, array $customerData);

    /**
     * @param int $customerId
     * @param array $item
     * @return int
     */
    public function createCustomerAddress($customerId, array $item);

    /**
     * @param int $customerAddressId
     * @param array $item
     * @return bool
     */
    public function updateCustomerAddress($customerAddressId, array $item);

    /**
     * Retrieve customer address info
     *
     * @param string $customerAddressId
     *
     * @return array
     */
    public function getCustomerAddressInfo($customerAddressId);

    /**
     * Get newsletter subscribers.
     *
     * @return \Iterator
     */
    public function getNewsletterSubscribers();

    /**
     * @param array $subscriberData
     *
     * @return array
     */
    public function createNewsletterSubscriber(array $subscriberData);

    /**
     * @param int $subscriberId
     * @param array $subscriberData
     *
     * @return array
     */
    public function updateNewsletterSubscriber($subscriberId, array $subscriberData);

    /**
     * @return bool
     */
    public function isSupportedExtensionVersion();

    /**
     * @return string
     */
    public function getExtensionVersion();

    /**
     * @return string
     */
    public function getMagentoVersion();
}
