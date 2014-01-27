<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider;

use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;

use OroCRM\Bundle\MagentoBundle\Provider\RegionConnector;

class RegionConnectorTest extends MagentoConnectorTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConnectorInstance(
        ContextRegistry $contextRegistry,
        LoggerStrategy $logger,
        ConnectorContextMediator $contextMediator
    ) {
        return new RegionConnector($contextRegistry, $logger, $contextMediator);
    }

    /**
     * {@inheritdoc}
     */
    protected function getIteratorGetterMethodName()
    {
        return 'getRegions';
    }

    public function testPublicInterface()
    {
        $contextMediatorMock = $this
            ->getMockBuilder('Oro\\Bundle\\IntegrationBundle\\Provider\\ConnectorContextMediator')
            ->disableOriginalConstructor()->getMock();

        $connector = $this->getConnectorInstance(new ContextRegistry(), new LoggerStrategy(), $contextMediatorMock);

        $this->assertEquals('region', $connector->getType());
        $this->assertEquals('mage_region_import', $connector->getImportJobName());
        $this->assertEquals('OroCRM\\Bundle\\MagentoBundle\\Entity\\Region', $connector->getImportEntityFQCN());
        $this->assertEquals('orocrm.magento.connector.region.label', $connector->getLabel());
    }
}
