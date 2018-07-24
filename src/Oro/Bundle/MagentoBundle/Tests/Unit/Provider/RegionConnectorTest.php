<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider;

use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Status;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;
use Oro\Bundle\MagentoBundle\Provider\Connector\RegionConnector;

class RegionConnectorTest extends MagentoConnectorTestCase
{
    /** @var array */
    protected $config = [
        'sync_settings' => [
            'mistiming_assumption_interval' => '2 minutes',
            'region_sync_interval' => '1 day',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    protected function getConnectorInstance(
        ContextRegistry $contextRegistry,
        LoggerStrategy $logger,
        ConnectorContextMediator $contextMediator
    ) {
        return new RegionConnector($contextRegistry, $logger, $contextMediator, $this->config);
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
        /** @var \PHPUnit\Framework\MockObject\MockObject|ConnectorContextMediator $contextMediatorMock */
        $contextMediatorMock = $this
            ->getMockBuilder('Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator')
            ->disableOriginalConstructor()->getMock();

        $connector = $this->getConnectorInstance(new ContextRegistry(), new LoggerStrategy(), $contextMediatorMock);

        $this->assertEquals('region_dictionary', $connector->getType());
        $this->assertEquals('mage_region_import', $connector->getImportJobName());
        $this->assertEquals('Oro\Bundle\MagentoBundle\Entity\Region', $connector->getImportEntityFQCN());
        $this->assertEquals('oro.magento.connector.region.label', $connector->getLabel());
    }

    /**
     * @param Status $expectedStatus
     * @param Channel $channel
     * @param string $connector
     */
    protected function expectLastCompletedStatusForConnector($expectedStatus, $channel, $connector)
    {
        $this->integrationRepositoryMock->expects($this->any())
            ->method('getLastStatusForConnector')
            ->with($channel, $connector, Status::STATUS_COMPLETED)
            ->will($this->returnValue($expectedStatus));
    }

    public function testSkippedIfSyncedDuringConfiguredInterval()
    {
        $transport = $this->createMock('Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface');
        $channel = new Channel();

        $connector = $this->getConnector($this->transportMock, $this->stepExecutionMock, $channel);

        $status = new Status();
        $status->setCode($status::STATUS_COMPLETED);
        $status->setConnector($connector->getType());
        $status->setDate(new \DateTime('-10 minutes', new \DateTimeZone('UTC')));

        $this->expectLastCompletedStatusForConnector($status, $channel, $connector->getType());

        $connector->setStepExecution($this->stepExecutionMock);

        $transport->expects($this->never())->method('getRegions');
        $this->assertInstanceOf('\EmptyIterator', $connector->getSourceIterator());
    }
}
