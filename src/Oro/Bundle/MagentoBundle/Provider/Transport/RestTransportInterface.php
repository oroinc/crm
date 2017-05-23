<?php

namespace Oro\Bundle\MagentoBundle\Provider\Transport;

use Oro\Bundle\IntegrationBundle\Exception\RuntimeException;
use Oro\Bundle\MagentoBundle\Exception\ExtensionRequiredException;

interface RestTransportInterface
{
    /**
     * @return array
     *
     * @throws RuntimeException | ExtensionRequiredException
     */
    public function doGetStoresRequest();

    /**
     * @return array
     *
     * @throws RuntimeException | ExtensionRequiredException
     */
    public function doGetWebsitesRequest();
}
