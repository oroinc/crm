<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

class InitialSyncProcessor extends AbstractInitialProcessor
{
    const INITIAL_SYNCED_TO = 'initialSyncedTo';

    /**
     * {@inheritdoc}
     */
    public function process(Integration $integration, $connector = null, array $parameters = [])
    {
        $parameters[self::INITIAL_SYNCED_TO] = $this->getInitialSyncedTo($integration);

        // Run incremental sync
        return parent::process($integration, $connector, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    protected function processConnectors(Integration $integration, array $parameters = [], callable $callback = null)
    {
        $callback = function ($connector) {
            return true; //$connector instanceof InitialConnectorInterface;
        };

        return parent::processConnectors($integration, $parameters, $callback);
    }
}
