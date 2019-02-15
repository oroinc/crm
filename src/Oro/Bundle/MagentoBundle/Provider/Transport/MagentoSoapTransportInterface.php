<?php

namespace Oro\Bundle\MagentoBundle\Provider\Transport;

/**
 * Provides interface for SOAP transport.
 */
interface MagentoSoapTransportInterface extends MagentoTransportInterface
{
    /**
     * @param string $action
     * @param array  $params
     *
     * @return mixed
     */
    public function call($action, $params = []);
}
