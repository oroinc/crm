<?php

namespace Oro\Bundle\MagentoBundle\Provider\Transport;

interface SoapTransportInterface
{
    /**
     * @param string $action
     * @param array  $params
     *
     * @return mixed
     */
    public function call($action, $params = []);
}
