<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider\Connector;

use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;

use OroCRM\Bundle\MagentoBundle\Provider\Connector\InitialOrderConnector;

class InitialOrderConnectorTest extends InitialConnectorTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConnectorInstance(
        ContextRegistry $contextRegistry,
        LoggerStrategy $logger,
        ConnectorContextMediator $contextMediator
    ) {
        return new InitialOrderConnector($contextRegistry, $logger, $contextMediator, $this->config);
    }

    /**
     * @return string
     */
    protected function getIteratorGetterMethodName()
    {
        return 'getOrders';
    }
}
