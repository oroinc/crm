<?php

namespace Oro\Bundle\MagentoBundle\Provider\Transport;

interface ServerTimeAwareInterface
{
    /**
     * Retrieve server time
     *
     * @return string|bool Returns false if impossible to get value
     */
    public function getServerTime();
}
