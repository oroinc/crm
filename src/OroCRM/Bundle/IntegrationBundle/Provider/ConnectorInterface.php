<?php

namespace OroCRM\Bundle\IntegrationBundle\Provider;

interface ConnectorInterface
{
    const SYNC_DIRECTION_PULL = 'pull';
    const SYNC_DIRECTION_PUSH = 'push';

    /**
     * Init connection using transport
     *
     * @return mixed
     */
    public function connect();

    /**
     * @param string|null $oneWay sync direction, default - initial sync, pull, if null - two way sync
     * @return mixed
     */
    public function sync($oneWay = self::SYNC_DIRECTION_PULL);
}
