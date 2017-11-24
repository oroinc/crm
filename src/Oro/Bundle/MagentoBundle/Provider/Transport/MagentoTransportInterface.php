<?php

namespace Oro\Bundle\MagentoBundle\Provider\Transport;

use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Exception\TransportException;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Provider\Iterator\UpdatedLoaderInterface;

interface MagentoTransportInterface extends TransportInterface, ServerTimeAwareInterface
{
    const ALIAS_GROUPS = 'groups';
    const ALIAS_STORES = 'stores';
    const ALIAS_WEBSITES = 'websites';
    const ALIAS_REGIONS = 'regions';

    const WEBSITE_CODE_SEPARATOR = ' / ';
    const WEBSITE_NAME_SEPARATOR = ', ';

    const TRANSPORT_ERROR_ADDRESS_DOES_NOT_EXIST = 102;

    /**
     * Allow initialize transport with additional settings (no multi attempts, debug logs, etc.)
     *
     * @param Transport $transportEntity
     * @param array     $clientExtraOptions
     *
     * @return void
     * @throws TransportException
     */
    public function initWithExtraOptions(Transport $transportEntity, array $clientExtraOptions);

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
     * Check that customer has unique email
     *
     * @param Customer $customer
     *
     * @return bool
     */
    public function isCustomerHasUniqueEmail(Customer $customer);

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
     *
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
     * Retrieve order info from magento.
     *
     * @param string $incrementId
     */
    public function getOrderInfo($incrementId);

    /**
     * Retrieve credit memos from magento.
     *
     * @return UpdatedLoaderInterface|\Iterator
     */
    public function getCreditMemos();

    /**
     * Retrieve credit memo info from magento.
     *
     * @param string $incrementId
     */
    public function getCreditMemoInfo($incrementId);

    /**
     * Create customer.
     *
     * @param array $customerData
     *
     * @return int ID of the created customer
     */
    public function createCustomer(array $customerData);

    /**
     * Update customer.
     *
     * @param int $customerId
     * @param array $customerData
     *
     * @return bool True if the customer is updated
     */
    public function updateCustomer($customerId, array $customerData);

    /**
     * Create customer address.
     *
     * @param int $customerId
     * @param array $item
     *
     * @return int
     */
    public function createCustomerAddress($customerId, array $item);

    /**
     * Update customer address.
     *
     * @param int $customerAddressId
     * @param array $item
     *
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
     * Create news letter subscriber.
     *
     * @param array $subscriberData
     *
     * @return array
     */
    public function createNewsletterSubscriber(array $subscriberData);

    /**
     * Update news letter subscriber.
     *
     * @param int $subscriberId
     * @param array $subscriberData
     *
     * @return array
     */
    public function updateNewsletterSubscriber($subscriberId, array $subscriberData);

    /**
     * Check that retrieved extension version from magento is supported.
     *
     * @return bool
     */
    public function isSupportedExtensionVersion();

    /**
     * Check that retrieved extension version from magento is supported order note functionality.
     *
     * @return boolean
     */
    public function isSupportedOrderNoteExtensionVersion();

    /**
     * Retrieve extension version.
     *
     * @return string
     */
    public function getExtensionVersion();

    /**
     * Retrieve magento version.
     *
     * @return string
     */
    public function getMagentoVersion();

    /**
     * Retrieve required extension version.
     *
     * @return string
     */
    public function getRequiredExtensionVersion();

    /**
     * Retrieve the required extension version from Magento that supports the order note functionality
     *
     * @return string | null
     */
    public function getOrderNoteRequiredExtensionVersion();

    /**
     * Revert initial state. Use for action check connection to execute request
     * to instance of magento.
     */
    public function resetInitialState();
}
