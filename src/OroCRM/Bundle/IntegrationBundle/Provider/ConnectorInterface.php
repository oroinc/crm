<?php

namespace OroCRM\Bundle\IntegrationBundle\Provider;

interface ConnectorInterface
{
    /**
     * @return mixed
     */
    public function connect();
}
