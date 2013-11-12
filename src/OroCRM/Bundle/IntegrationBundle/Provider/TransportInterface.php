<?php

namespace OroCRM\Bundle\IntegrationBundle\Provider;

interface TransportInterface
{
    /**
     * @param array $settings
     * @return mixed
     */
    public function connect(array $settings);

    /**
     * @param $action
     * @param array $params
     * @return mixed
     */
    public function fetch($action, $params = []);

    /**
     * @param $action
     * @param array $params
     * @return mixed
     */
    public function send($action, $params = []);
}
