<?php

namespace Oro\Bundle\MagentoBundle\Provider\Transport;

use Oro\Bundle\IntegrationBundle\Exception\RuntimeException;

interface RestTransportInterface
{
    /**
     * @return array
     *
     * @throws RuntimeException
     */
    public function doGetStoresRequest();

    /**
     * @return array
     *
     * @throws RuntimeException
     */
    public function doGetWebsitesRequest();
}
