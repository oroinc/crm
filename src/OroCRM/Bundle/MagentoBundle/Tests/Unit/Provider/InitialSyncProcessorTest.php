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
            $this->logger,
            ['sync_settings' => ['initial_import_step_interval' => '2 days']]
        );

        $this->processor->setChannelClassName('Oro\IntegrationBundle\Entity\Channel');
    }

    public function testProcess()
    {
        $connector = 'testConnector_initial';
        $connectors = [$connector];
        $interval = new \DateInterval('P2D');
        $initialStartDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $syncedTo = clone $initialStartDate;
        $syncedTo->sub($interval);
        $syncStartDate = clone $syncedTo;
        $syncStartDate->sub($interval);

        $realConnector = new InitialConnector();
        $integration = $this->getIntegration($connectors, $syncStartDate, $realConnector);

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
        $this->assertProcessCalls();
        $this->assertExecuteJob(
            [
                'processorAlias' => false,
                'entityName' => 'testEntity',
                'channel' => 'testChannel',
                'channelType' => 'testChannelType',
                'start_sync_date' => $syncStartDate,
                AbstractInitialProcessor::INTERVAL => $interval,
                AbstractInitialProcessor::INITIAL_SYNCED_TO => $syncedTo,
            ]
        );

        $this->processor->process($integration);
    }

    public function testProcessOverDate()
    {
        $connector = 'testConnector_initial';
        $connectors = [$connector];
        $interval = new \DateInterval('P2D');
        $subInterval = new \DateInterval('P1D');
        $initialStartDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $syncedTo = clone $initialStartDate;
        $syncedTo->sub($subInterval);
        $syncStartDate = clone $syncedTo;
        $syncStartDate->sub($subInterval);

        $realConnector = new InitialConnector();
        $integration = $this->getIntegration($connectors, $syncStartDate, $realConnector);

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
        $status->expects($this->any())
            ->method('setData')
            ->with(
                $this->callback(
                    function ($data) use ($syncStartDate) {
                        $this->assertArrayHasKey('initialSyncedTo', $data);

                        $this->assertEquals(['initialSyncedTo' => $syncStartDate->format(\DateTime::ISO8601)], $data);

                        return true;
                    }
                )
            );

        $this->assertConnectorStatusCall($integration, $connector, $status);
        $this->assertProcessCalls();
        $this->assertExecuteJob(
            [
                'processorAlias' => false,
                'entityName' => 'testEntity',
                'channel' => 'testChannel',
                'channelType' => 'testChannelType',
                'start_sync_date' => $syncStartDate,
                AbstractInitialProcessor::INTERVAL => $interval,
                AbstractInitialProcessor::INITIAL_SYNCED_TO => $syncedTo,
            ]
        );

        $this->processor->process($integration);
    }

    public function testProcessFirst()
    {
        $connector = 'testConnector_initial';
        $connectors = [$connector];
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $interval = new \DateInterval('P2D');
        $initialStartDate = clone $now;
        $syncedTo = clone $initialStartDate;
        $syncedTo->sub($interval);
        $syncStartDate = clone $syncedTo;
        $syncStartDate->sub($interval);

        $integration = $this->getIntegration($connectors, $syncStartDate);

        $status = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Status')
            ->disableOriginalConstructor()
            ->getMock();
        $status->expects($this->atLeastOnce())
            ->method('getData')
            ->will($this->returnValue([]));
        $status->expects($this->any())
            ->method('setData')
            ->with(
                $this->callback(
                    function ($data) use ($now, $interval) {
                        $this->assertArrayHasKey('initialSyncedTo', $data);

                        $date = \DateTime::createFromFormat(
                            \DateTime::ISO8601,
                            $data['initialSyncedTo'],
                            new \DateTimeZone('UTC')
                        );

                        $this->assertEquals($date, $now->sub($interval));

                        return true;
                    }
                )
            );

        $this->assertConnectorStatusCall($integration, $connector, $status);
        $this->assertProcessCalls();
        $this->assertExecuteJob(
            [
                'processorAlias' => false,
                'entityName' => 'testEntity',
                'channel' => 'testChannel',
                'channelType' => 'testChannelType',
                'start_sync_date' => $syncStartDate,
                AbstractInitialProcessor::INTERVAL => $interval,
            ]
        );

        $this->processor->process($integration);
    }
}
