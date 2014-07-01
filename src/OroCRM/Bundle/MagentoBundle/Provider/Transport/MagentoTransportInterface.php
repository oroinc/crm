<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Transport;

use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\UpdatedLoaderInterface;

interface MagentoTransportInterface extends TransportInterface
{
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
     * Retrieve customer address list
     *
     * @param Customer $customer
     *
     * @return array
     */
    public function getCustomerAddresses(Customer $customer);

    /**
     * Parse exception from remote side and returns generic code
     *
     * @param \Exception $e
     *
     * @return int
     */
    public function getErrorCode(\Exception $e);
}
