<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider;

use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;
use Oro\Bundle\MagentoBundle\Provider\Connector\CreditMemoConnector;

class CreditMemoConnectorTest extends MagentoConnectorTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConnectorInstance(
        ContextRegistry $contextRegistry,
        LoggerStrategy $logger,
        ConnectorContextMediator $contextMediator
    ) {
        return new CreditMemoConnector($contextRegistry, $logger, $contextMediator, $this->config);
    }

    /**
     * {@inheritdoc}
     */
    protected function getIteratorGetterMethodName()
    {
        return 'getCreditMemos';
    }

    public function testPublicInterface()
    {
        $contextMediatorMock = $this
            ->getMockBuilder('Oro\\Bundle\\IntegrationBundle\\Provider\\ConnectorContextMediator')
            ->disableOriginalConstructor()->getMock();

        $connector = $this->getConnectorInstance(new ContextRegistry(), new LoggerStrategy(), $contextMediatorMock);

        $this->assertEquals('credit_memo', $connector->getType());
        $this->assertEquals('mage_credit_memo_import', $connector->getImportJobName());
        $this->assertEquals('Oro\\Bundle\\MagentoBundle\\Entity\\CreditMemo', $connector->getImportEntityFQCN());
        $this->assertEquals('oro.magento.connector.credit_memo.label', $connector->getLabel());
    }
}
