<?php

namespace OroCRM\Bundle\IntegrationBundle\Provider;

interface ConnectorInterface
{
    /**
     * Init connection using transport
     *
     * @return mixed
     */
    public function connect();
}
