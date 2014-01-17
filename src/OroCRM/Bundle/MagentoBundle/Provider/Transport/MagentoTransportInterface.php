<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Transport;

use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;

use OroCRM\Bundle\MagentoBundle\Provider\Iterator\DataIteratorInterface;

interface MagentoTransportInterface extends TransportInterface
{
    const WEBSITE_CODE_SEPARATOR = ' / ';
    const WEBSITE_NAME_SEPARATOR = ', ';

    /**
     * Return true if oro extension installed on remote instance
     *
     * @return bool
     */
    public function isExtensionInstalled();

    /**
     * Retrieve orders from magento
     *
     * @return DataIteratorInterface
     */
    public function getOrders();

    /**
     * @return \Iterator
     */
    public function getStores();

    /**
     * @return \Iterator
     */
    public function getWebsites();
}
