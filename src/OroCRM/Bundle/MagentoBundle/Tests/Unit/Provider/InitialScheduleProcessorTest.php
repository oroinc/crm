<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider;

use OroCRM\Bundle\MagentoBundle\Command\InitialSyncCommand;
use OroCRM\Bundle\MagentoBundle\Provider\AbstractInitialProcessor;
use OroCRM\Bundle\MagentoBundle\Provider\AbstractMagentoConnector;
use OroCRM\Bundle\MagentoBundle\Provider\InitialScheduleProcessor;

class InitialScheduleProcessorTest extends AbstractSyncProcessorTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->processor = new InitialScheduleProcessor(
            $this->registry,
            $this->processorRegistry,
            $this->jobExecutor,
            $this->typesRegistry,
            $this->eventDispatcher,
            $this->logger
        );
    }

    public function testProcessFirstInitial()
    {
        $connector  = 'testConnector';
        $connectors = [$connector];
        $integration = $this->getIntegration($connectors);

        $this->em->expects($this->exactly(2))
            ->method('persist');
        $this->em->expects($this->exactly(2))
            ->method('flush');
        $this->repository->expects($this->once())
            ->method('getRunningSyncJobsCount')
            ->with(InitialSyncCommand::COMMAND_NAME, $integration->getId());

        $syncSettings = $this->assertIntegrationSettingsCall($integration);

        $this->assertFalse($syncSettings->offsetExists(InitialScheduleProcessor::INITIAL_SYNC_STARTED));
        $this->assertFalse($syncSettings->offsetExists(InitialScheduleProcessor::INITIAL_SYNC_START_DATE));

        $this->assertProcessCalls();
        $this->assertExecuteJob();

        $this->processor->process($integration);

        $this->assertTrue($syncSettings->offsetExists(InitialScheduleProcessor::INITIAL_SYNC_STARTED));
        $this->assertTrue($syncSettings->offsetExists(InitialScheduleProcessor::INITIAL_SYNC_START_DATE));
        $this->assertTrue($syncSettings->offsetGet(InitialScheduleProcessor::INITIAL_SYNC_STARTED));
    }

    public function testProcessExisting()
    {
        $syncedTo = new \DateTime('2011-01-02 12:13:14', new \DateTimeZone('UTC'));
        $initialStartDate = new \DateTime('2011-01-03 12:13:14', new \DateTimeZone('UTC'));
        $syncStartDate = new \DateTime('2000-01-01 00:00:00', new \DateTimeZone('UTC'));

        $connector  = 'testConnector';
        $connectors = [$connector];
        $integration = $this->getIntegration($connectors, ['start_sync_date' => $syncStartDate]);

        $this->em->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf('JMS\JobQueueBundle\Entity\Job'));
        $this->em->expects($this->once())
            ->method('flush')
            ->with($this->isInstanceOf('JMS\JobQueueBundle\Entity\Job'));
        $this->repository->expects($this->once())
            ->method('getRunningSyncJobsCount')
            ->with(InitialSyncCommand::COMMAND_NAME, $integration->getId());

        $settings = [
            AbstractInitialProcessor::INITIAL_SYNC_START_DATE => $initialStartDate->format(\DateTime::ISO8601),
            InitialScheduleProcessor::INITIAL_SYNC_STARTED => true,
            AbstractInitialProcessor::INITIAL_SYNCED_TO => $syncedTo
        ];
        $syncSettings = $this->assertIntegrationSettingsCall($integration, $settings);

        $this->assertTrue($syncSettings->offsetExists(InitialScheduleProcessor::INITIAL_SYNC_STARTED));
        $this->assertTrue($syncSettings->offsetExists(InitialScheduleProcessor::INITIAL_SYNC_START_DATE));

        $this->assertProcessCalls();
        $this->assertExecuteJob(
            [
                'processorAlias' => false,
                'entityName'     => 'testEntity',
                'channel'        => 'testChannel',
                'channelType'    => 'testChannelType',
                AbstractMagentoConnector::LAST_SYNC_KEY => $initialStartDate
            ]
        );

        $this->processor->process($integration);

        $this->assertTrue($syncSettings->offsetExists(InitialScheduleProcessor::INITIAL_SYNC_STARTED));
        $this->assertTrue($syncSettings->offsetExists(InitialScheduleProcessor::INITIAL_SYNC_START_DATE));
        $this->assertTrue($syncSettings->offsetGet(InitialScheduleProcessor::INITIAL_SYNC_STARTED));
        $this->assertEquals(
            $initialStartDate->format(\DateTime::ISO8601),
            $syncSettings->offsetGet(InitialScheduleProcessor::INITIAL_SYNC_START_DATE)
        );
    }

    public function testProcessJobRunning()
    {
        $syncedTo = new \DateTime('2011-01-02 12:13:14', new \DateTimeZone('UTC'));
        $initialStartDate = new \DateTime('2011-01-03 12:13:14', new \DateTimeZone('UTC'));
        $syncStartDate = new \DateTime('2000-01-01 00:00:00', new \DateTimeZone('UTC'));

        $connector  = 'testConnector';
        $connectors = [$connector];
        $integration = $this->getIntegration($connectors, ['start_sync_date' => $syncStartDate]);

        $this->em->expects($this->never())
            ->method('persist');
        $this->em->expects($this->never())
            ->method('flush');
        $this->repository->expects($this->once())
            ->method('getRunningSyncJobsCount')
            ->with(InitialSyncCommand::COMMAND_NAME, $integration->getId())
            ->will($this->returnValue(2));

        $settings = [
            AbstractInitialProcessor::INITIAL_SYNC_START_DATE => $initialStartDate->format(\DateTime::ISO8601),
            InitialScheduleProcessor::INITIAL_SYNC_STARTED => true,
            AbstractInitialProcessor::INITIAL_SYNCED_TO => $syncedTo
        ];
        $syncSettings = $this->assertIntegrationSettingsCall($integration, $settings);

        $this->assertTrue($syncSettings->offsetExists(InitialScheduleProcessor::INITIAL_SYNC_STARTED));
        $this->assertTrue($syncSettings->offsetExists(InitialScheduleProcessor::INITIAL_SYNC_START_DATE));

        $this->assertProcessCalls();
        $this->assertExecuteJob(
            [
                'processorAlias' => false,
                'entityName'     => 'testEntity',
                'channel'        => 'testChannel',
                'channelType'    => 'testChannelType',
                AbstractMagentoConnector::LAST_SYNC_KEY => $initialStartDate
            ]
        );

        $this->processor->process($integration);

        $this->assertTrue($syncSettings->offsetExists(InitialScheduleProcessor::INITIAL_SYNC_STARTED));
        $this->assertTrue($syncSettings->offsetExists(InitialScheduleProcessor::INITIAL_SYNC_START_DATE));
        $this->assertTrue($syncSettings->offsetGet(InitialScheduleProcessor::INITIAL_SYNC_STARTED));
        $this->assertEquals(
            $initialStartDate->format(\DateTime::ISO8601),
            $syncSettings->offsetGet(InitialScheduleProcessor::INITIAL_SYNC_START_DATE)
        );
    }

    public function testProcessInitialSynced()
    {
        $initialStartDate = new \DateTime('2011-01-03 12:13:14', new \DateTimeZone('UTC'));
        $syncStartDate = new \DateTime('2000-01-01 00:00:00', new \DateTimeZone('UTC'));
        $syncedTo = $syncStartDate->sub(new \DateInterval('P1D'));

        $connector  = 'testConnector';
        $connectors = [$connector];
        $integration = $this->getIntegration($connectors, ['start_sync_date' => $syncStartDate]);

        $this->em->expects($this->never())
            ->method('persist');
        $this->em->expects($this->never())
            ->method('flush');
        $this->repository->expects($this->once())
            ->method('getRunningSyncJobsCount')
            ->with(InitialSyncCommand::COMMAND_NAME, $integration->getId());

        $settings = [
            AbstractInitialProcessor::INITIAL_SYNC_START_DATE => $initialStartDate->format(\DateTime::ISO8601),
            InitialScheduleProcessor::INITIAL_SYNC_STARTED => true,
            AbstractInitialProcessor::INITIAL_SYNCED_TO => $syncedTo
        ];
        $syncSettings = $this->assertIntegrationSettingsCall($integration, $settings);

        $this->assertTrue($syncSettings->offsetExists(InitialScheduleProcessor::INITIAL_SYNC_STARTED));
        $this->assertTrue($syncSettings->offsetExists(InitialScheduleProcessor::INITIAL_SYNC_START_DATE));

        $this->assertProcessCalls();
        $this->assertExecuteJob(
            [
                'processorAlias' => false,
                'entityName'     => 'testEntity',
                'channel'        => 'testChannel',
                'channelType'    => 'testChannelType',
                AbstractMagentoConnector::LAST_SYNC_KEY => $initialStartDate
            ]
        );

        $this->processor->process($integration);

        $this->assertTrue($syncSettings->offsetExists(InitialScheduleProcessor::INITIAL_SYNC_STARTED));
        $this->assertTrue($syncSettings->offsetExists(InitialScheduleProcessor::INITIAL_SYNC_START_DATE));
        $this->assertTrue($syncSettings->offsetGet(InitialScheduleProcessor::INITIAL_SYNC_STARTED));
        $this->assertEquals(
            $initialStartDate->format(\DateTime::ISO8601),
            $syncSettings->offsetGet(InitialScheduleProcessor::INITIAL_SYNC_START_DATE)
        );
    }
}
