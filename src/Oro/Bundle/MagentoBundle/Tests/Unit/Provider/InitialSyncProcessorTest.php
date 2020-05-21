<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\MagentoBundle\Provider\AbstractInitialProcessor;
use Oro\Bundle\MagentoBundle\Provider\InitialSyncProcessor;
use Oro\Bundle\MagentoBundle\Tests\Unit\Provider\Stub\InitialConnector;

class InitialSyncProcessorTest extends AbstractSyncProcessorTest
{
    /** @var InitialSyncProcessor */
    protected $processor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->processor = new InitialSyncProcessor(
            $this->registry,
            $this->processorRegistry,
            $this->jobExecutor,
            $this->typesRegistry,
            $this->eventDispatcher,
            $this->logger,
            ['sync_settings' => ['import_step_interval' => '2 days']]
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
                        InitialSyncProcessor::SYNCED_TO => $syncedTo->format(\DateTime::ISO8601)
                    ]
                )
            );

        $dictionaryConnector = $this->getMockBuilder('Oro\Bundle\MagentoBundle\Provider\Connector\WebsiteConnector')
            ->disableOriginalConstructor()
            ->getMock();
        $dictionaryConnector->expects($this->any())
            ->method('getType')
            ->willReturn('dictionary');

        $dictionaryConnector->expects($this->any())
            ->method('getImportJobName')
            ->willReturn('test job');

        $this->typesRegistry->expects($this->any())
            ->method('getRegisteredConnectorsTypes')
            ->willReturn(new ArrayCollection(['dictionaryConnector' => $dictionaryConnector]));

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
                AbstractInitialProcessor::SYNCED_TO => $syncedTo
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
                        InitialSyncProcessor::SYNCED_TO => $syncedTo->format(\DateTime::ISO8601)
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

        $dictionaryConnector = $this->getMockBuilder('Oro\Bundle\MagentoBundle\Provider\Connector\WebsiteConnector')
            ->disableOriginalConstructor()
            ->getMock();
        $dictionaryConnector->expects($this->any())
            ->method('getImportJobName')
            ->willReturn('test job');
        $dictionaryConnector->expects($this->any())
            ->method('getType')
            ->willReturn('dictionary');

        $this->typesRegistry->expects($this->any())
            ->method('getRegisteredConnectorsTypes')
            ->willReturn(new ArrayCollection(['dictionaryConnector' => $dictionaryConnector]));

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
                AbstractInitialProcessor::SYNCED_TO => $syncedTo
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
        $syncedTo = clone $now;
        $syncStartDate = clone $now;
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
                    function ($data) use ($syncedTo, $interval) {
                        $this->assertArrayHasKey('initialSyncedTo', $data);

                        $date = \DateTime::createFromFormat(
                            \DateTime::ISO8601,
                            $data['initialSyncedTo'],
                            new \DateTimeZone('UTC')
                        );

                        $syncedTo = clone $syncedTo;
                        $syncedTo = $syncedTo->sub($interval);
                        $this->assertEquals($syncedTo->format('Y-m-d'), $date->format('Y-m-d'));

                        return true;
                    }
                )
            );

        $dictionaryConnector = $this->getMockBuilder('Oro\Bundle\MagentoBundle\Provider\Connector\WebsiteConnector')
            ->disableOriginalConstructor()
            ->getMock();
        $dictionaryConnector->expects($this->any())
            ->method('getImportJobName')
            ->willReturn('test job');
        $dictionaryConnector->expects($this->any())
            ->method('getType')
            ->willReturn('dictionary');

        $this->typesRegistry->expects($this->any())
            ->method('getRegisteredConnectorsTypes')
            ->willReturn(new ArrayCollection(['dictionaryConnector' => $dictionaryConnector]));

        $this->assertConnectorStatusCall($integration, $connector, $status);
        $this->assertProcessCalls();
        $this->assertExecuteJob(
            [
                'processorAlias' => false,
                'entityName' => 'testEntity',
                'channel' => 'testChannel',
                'channelType' => 'testChannelType',
                'start_sync_date' => $now,
                AbstractInitialProcessor::INTERVAL => $interval
            ]
        );

        $this->processor->process($integration);
    }
}
