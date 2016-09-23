<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider\Connector;

use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;
use Oro\Bundle\MagentoBundle\Provider\Connector\InitialCustomerConnector;

class InitialCustomerConnectorTest extends InitialConnectorTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConnectorInstance(
        ContextRegistry $contextRegistry,
        LoggerStrategy $logger,
        ConnectorContextMediator $contextMediator
    ) {
        return new InitialCustomerConnector($contextRegistry, $logger, $contextMediator, $this->config);
    }

    /**
     * @return string
     */
    protected function getIteratorGetterMethodName()
    {
        return 'getCustomers';
    }
}
