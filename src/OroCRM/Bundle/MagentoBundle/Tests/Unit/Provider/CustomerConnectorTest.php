<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider;

use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;

use OroCRM\Bundle\MagentoBundle\Provider\CustomerConnector;

class CustomerConnectorTest extends MagentoConnectorTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConnectorInstance(
        ContextRegistry $contextRegistry,
        LoggerStrategy $logger,
        ConnectorContextMediator $contextMediator
    ) {
        return new CustomerConnector($contextRegistry, $logger, $contextMediator, $this->config);
    }

    /**
     * {@inheritdoc}
     */
    protected function getIteratorGetterMethodName()
    {
        return 'getCustomers';
    }

    public function testPublicInterface()
    {
        $contextMediatorMock = $this
            ->getMockBuilder('Oro\\Bundle\\IntegrationBundle\\Provider\\ConnectorContextMediator')
            ->disableOriginalConstructor()->getMock();

        $connector = $this->getConnectorInstance(new ContextRegistry(), new LoggerStrategy(), $contextMediatorMock);

        $this->assertEquals('customer', $connector->getType());
        $this->assertEquals('mage_customer_import', $connector->getImportJobName());
        $this->assertEquals('OroCRM\\Bundle\\MagentoBundle\\Entity\\Customer', $connector->getImportEntityFQCN());
        $this->assertEquals('orocrm.magento.connector.customer.label', $connector->getLabel());
    }

    protected function supportsForceMode()
    {
        return true;
    }
}
