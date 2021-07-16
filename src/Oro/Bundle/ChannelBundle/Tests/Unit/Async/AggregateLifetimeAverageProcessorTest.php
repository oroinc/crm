<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Async;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Oro\Bundle\ChannelBundle\Async\AggregateLifetimeAverageProcessor;
use Oro\Bundle\ChannelBundle\Async\Topics;
use Oro\Bundle\ChannelBundle\Entity\Repository\LifetimeValueAverageAggregationRepository;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Test\JobRunner;
use Oro\Component\MessageQueue\Transport\Message;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use Oro\Component\Testing\ClassExtensionTrait;

class AggregateLifetimeAverageProcessorTest extends \PHPUnit\Framework\TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementMessageProcessorInterface()
    {
        $this->assertClassImplements(MessageProcessorInterface::class, AggregateLifetimeAverageProcessor::class);
    }

    public function testShouldImplementTopicSubscriberInterface()
    {
        $this->assertClassImplements(TopicSubscriberInterface::class, AggregateLifetimeAverageProcessor::class);
    }

    public function testShouldSubscribeOnChannelStatusChangedTopic()
    {
        $this->assertEquals(
            [Topics::AGGREGATE_LIFETIME_AVERAGE],
            AggregateLifetimeAverageProcessor::getSubscribedTopics()
        );
    }

    public function testCouldBeConstructedWithDoctrineAndLocaleSettingsAsArguments()
    {
        new AggregateLifetimeAverageProcessor(
            $this->createRegistryStub(),
            $this->createLocaleSettingsMock(),
            new JobRunner()
        );
    }

    public function testThrowIfMessageBodyInvalidJson()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The malformed json given.');

        $processor = new AggregateLifetimeAverageProcessor(
            $this->createRegistryStub(),
            $this->createLocaleSettingsMock(),
            new JobRunner()
        );

        $message = new Message();
        $message->setBody('[}');

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $processor->process($message, $session);
    }

    public function testShouldDoAggregateAndWithoutForceByDefault()
    {
        $localeSettings = $this->createLocaleSettingsMock();
        $localeSettings
            ->expects($this->once())
            ->method('getTimeZone')
            ->willReturn('theTimeZone')
        ;

        $repositoryMock = $this->createLifetimeValueAverageAggregationRepositoryMock();
        $repositoryMock
            ->expects($this->never())
            ->method('clearTableData')
        ;
        $repositoryMock
            ->expects($this->once())
            ->method('aggregate')
            ->with('theTimeZone', false)
        ;

        $registryStub = $this->createRegistryStub($repositoryMock);

        $processor = new AggregateLifetimeAverageProcessor(
            $registryStub,
            $localeSettings,
            new JobRunner()
        );

        $message = new Message();
        $message->setBody(JSON::encode([]));

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::ACK, $status);
    }

    public function testShouldClearTableBeforeAggregateIfForceTrue()
    {
        $localeSettings = $this->createLocaleSettingsMock();
        $localeSettings
            ->expects($this->once())
            ->method('getTimeZone')
            ->willReturn('theTimeZone')
        ;

        $repositoryMock = $this->createLifetimeValueAverageAggregationRepositoryMock();
        $repositoryMock
            ->expects($this->once())
            ->method('clearTableData')
            ->with(false)
        ;
        $repositoryMock
            ->expects($this->once())
            ->method('aggregate')
            ->with('theTimeZone', true)
        ;

        $registryStub = $this->createRegistryStub($repositoryMock);

        $processor = new AggregateLifetimeAverageProcessor(
            $registryStub,
            $localeSettings,
            new JobRunner()
        );

        $message = new Message();
        $message->setBody(JSON::encode([
            'force' => true
        ]));

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::ACK, $status);
    }

    public function testShouldTruncateTableBeforeAggregateIfForceTrue()
    {
        $localeSettings = $this->createLocaleSettingsMock();
        $localeSettings
            ->expects($this->once())
            ->method('getTimeZone')
            ->willReturn('theTimeZone')
        ;

        $repositoryMock = $this->createLifetimeValueAverageAggregationRepositoryMock();
        $repositoryMock
            ->expects($this->once())
            ->method('clearTableData')
            ->with(true)
        ;
        $repositoryMock
            ->expects($this->once())
            ->method('aggregate')
            ->with('theTimeZone', true)
        ;

        $registryStub = $this->createRegistryStub($repositoryMock);

        $processor = new AggregateLifetimeAverageProcessor(
            $registryStub,
            $localeSettings,
            new JobRunner()
        );

        $message = new Message();
        $message->setBody(JSON::encode([
            'force' => true,
            'use_truncate' => false,
        ]));

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::ACK, $status);
    }

    public function testShouldRunAggregateLifetimeAverageAsUniqueJob()
    {
        $localeSettings = $this->createLocaleSettingsMock();
        $localeSettings
            ->expects($this->once())
            ->method('getTimeZone')
            ->willReturn('theTimeZone')
        ;

        $repositoryMock = $this->createLifetimeValueAverageAggregationRepositoryMock();
        $repositoryMock
            ->expects($this->once())
            ->method('clearTableData')
            ->with(true)
        ;
        $repositoryMock
            ->expects($this->once())
            ->method('aggregate')
            ->with('theTimeZone', true)
        ;

        $registryStub = $this->createRegistryStub($repositoryMock);

        $jobRunner = new JobRunner();

        $processor = new AggregateLifetimeAverageProcessor(
            $registryStub,
            $localeSettings,
            $jobRunner
        );

        $message = new Message();
        $message->setMessageId('theMessageId');
        $message->setBody(JSON::encode([
            'force' => true,
            'use_truncate' => false,
        ]));

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $processor->process($message, $session);

        $uniqueJobs = $jobRunner->getRunUniqueJobs();
        self::assertCount(1, $uniqueJobs);
        self::assertEquals('oro_channel:aggregate_lifetime_average', $uniqueJobs[0]['jobName']);
        self::assertEquals('theMessageId', $uniqueJobs[0]['ownerId']);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|LocaleSettings
     */
    private function createLocaleSettingsMock()
    {
        return $this->createMock(LocaleSettings::class);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|LifetimeValueAverageAggregationRepository
     */
    private function createLifetimeValueAverageAggregationRepositoryMock()
    {
        return $this->createMock(LifetimeValueAverageAggregationRepository::class);
    }

    /**
     * @param ObjectRepository $entityRepository
     * @return \PHPUnit\Framework\MockObject\MockObject|ManagerRegistry
     */
    private function createRegistryStub($entityRepository = null)
    {
        $registryMock = $this->createMock(ManagerRegistry::class);
        $registryMock
            ->expects($this->any())
            ->method('getRepository')
            ->willReturn($entityRepository)
        ;

        return $registryMock;
    }
}
