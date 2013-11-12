<?php

namespace OroCRM\Bundle\IntegrationBundle\Provider;

interface TransportInterface
{
    /**
     * @param array $settings
     * @return mixed
     */
    public function init(array $settings);
}
