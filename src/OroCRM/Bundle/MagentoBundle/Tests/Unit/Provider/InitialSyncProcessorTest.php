<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider;

use OroCRM\Bundle\MagentoBundle\Provider\AbstractInitialProcessor;
use OroCRM\Bundle\MagentoBundle\Provider\InitialSyncProcessor;
use OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider\Stub\InitialConnector;

class InitialSyncProcessorTest extends AbstractSyncProcessorTest
{
    /** @var InitialSyncProcessor */
    protected $processor;

    protected function setUp()
    {
        parent::setUp();

        $this->processor = new InitialSyncProcessor(
            $this->registry,
            $this->processorRegistry,
            $this->jobExecutor,
            $this->typesRegistry,
            $this->eventDispatcher,
            $this->logger
        );
    }

    public function testProcess()
    {
        $connector = 'testConnector_initial';
        $connectors = [$connector];
        $syncStartDate = new \DateTime('2000-01-01 00:00:00', new \DateTimeZone('UTC'));
        $syncedTo = new \DateTime('2011-01-02 12:13:14', new \DateTimeZone('UTC'));
        $initialStartDate = new \DateTime('2011-01-03 12:13:14', new \DateTimeZone('UTC'));

        $realConnector = new InitialConnector();
        $integration = $this->getIntegration($connectors, ['start_sync_date' => $syncStartDate], $realConnector);

        $status = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Status')
            ->disableOriginalConstructor()
            ->getMock();
        $status->expects($this->atLeastOnce())
            ->method('getData')
            ->will(
                $this->returnValue(
                    [
                        InitialSyncProcessor::INITIAL_SYNCED_TO => $syncedTo->format(\DateTime::ISO8601)
                    ]
                )
            );

        $this->assertConnectorStatusCall($integration, $connector, $status);
        $settings = [
            AbstractInitialProcessor::INITIAL_SYNC_START_DATE => $initialStartDate->format(\DateTime::ISO8601)
        ];
        $this->assertIntegrationSettingsCall($integration, $settings);
        $this->assertProcessCalls();
        $this->assertExecuteJob(
            [
                'processorAlias' => false,
                'entityName' => 'testEntity',
                'channel' => 'testChannel',
                'channelType' => 'testChannelType',
                AbstractInitialProcessor::INITIAL_SYNCED_TO => $syncedTo,
                'start_sync_date' => $syncStartDate
            ]
        );

        $this->processor->process($integration);
    }

    public function testProcessFirst()
    {
        $connector = 'testConnector_initial';
        $connectors = [$connector];
        $syncStartDate = new \DateTime('2000-01-01 00:00:00', new \DateTimeZone('UTC'));
        $initialStartDate = new \DateTime('2011-01-03 12:13:14', new \DateTimeZone('UTC'));

        $integration = $this->getIntegration($connectors, ['start_sync_date' => $syncStartDate]);

        $this->assertConnectorStatusCall($integration, $connector);
        $settings = [
            AbstractInitialProcessor::INITIAL_SYNC_START_DATE => $initialStartDate->format(\DateTime::ISO8601)
        ];
        $this->assertIntegrationSettingsCall($integration, $settings);
        $this->assertProcessCalls();
        $this->assertExecuteJob(
            [
                'processorAlias' => false,
                'entityName' => 'testEntity',
                'channel' => 'testChannel',
                'channelType' => 'testChannelType',
                AbstractInitialProcessor::INITIAL_SYNCED_TO => $initialStartDate,
                'start_sync_date' => $syncStartDate
            ]
        );

        $this->processor->process($integration);
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $integration
     * @param string $connector
     * @param null|object $status
     */
    protected function assertConnectorStatusCall($integration, $connector, $status = null)
    {
        $this->repository->expects($this->atLeastOnce())
            ->method('getLastStatusForConnector')
            ->with($integration, $connector)
            ->will($this->returnValue($status));
    }
}
