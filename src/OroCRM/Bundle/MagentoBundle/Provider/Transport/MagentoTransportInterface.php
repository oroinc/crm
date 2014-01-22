<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Transport;

use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;

use OroCRM\Bundle\MagentoBundle\Provider\Iterator\UpdatedLoaderInterface;

interface MagentoTransportInterface extends TransportInterface
{
    const WEBSITE_CODE_SEPARATOR = ' / ';
    const WEBSITE_NAME_SEPARATOR = ', ';

    /**
     * Return true if oro bridge extension installed on remote instance
     *
     * @return bool
     */
    public function isExtensionInstalled();

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
}
