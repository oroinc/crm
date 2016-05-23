<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Async;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\Null\NullMessage;
use Oro\Component\MessageQueue\Transport\Null\NullSession;
use Oro\Component\MessageQueue\Util\JSON;
use Oro\Component\Testing\ClassExtensionTrait;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use OroCRM\Bundle\ChannelBundle\Async\AggregateLifetimeAverageProcessor;
use OroCRM\Bundle\ChannelBundle\Async\Topics;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Entity\Repository\LifetimeValueAverageAggregationRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AggregateLifetimeAverageProcessorTest extends \PHPUnit_Framework_TestCase
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
        new AggregateLifetimeAverageProcessor($this->createRegistryStub(), $this->createLocaleSettingsMock());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage The malformed json given.
     */
    public function testThrowIfMessageBodyInvalidJson()
    {
        $processor = new AggregateLifetimeAverageProcessor(
            $this->createRegistryStub(),
            $this->createLocaleSettingsMock()
        );

        $message = new NullMessage();
        $message->setBody('[}');

        $processor->process($message, new NullSession());
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

        $processor = new AggregateLifetimeAverageProcessor($registryStub, $localeSettings);

        $message = new NullMessage();
        $message->setBody(JSON::encode([]));

        $status = $processor->process($message, new NullSession());

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

        $processor = new AggregateLifetimeAverageProcessor($registryStub, $localeSettings);

        $message = new NullMessage();
        $message->setBody(JSON::encode([
            'force' => true
        ]));

        $status = $processor->process($message, new NullSession());

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

        $processor = new AggregateLifetimeAverageProcessor($registryStub, $localeSettings);

        $message = new NullMessage();
        $message->setBody(JSON::encode([
            'force' => true,
            'clear_table_use_delete' => true,
        ]));

        $status = $processor->process($message, new NullSession());

        $this->assertEquals(MessageProcessorInterface::ACK, $status);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LocaleSettings
     */
    private function createLocaleSettingsMock()
    {
        return $this->getMock(LocaleSettings::class, [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LifetimeValueAverageAggregationRepository
     */
    private function createLifetimeValueAverageAggregationRepositoryMock()
    {
        return $this->getMock(LifetimeValueAverageAggregationRepository::class, [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|RegistryInterface
     */
    private function createRegistryStub($entityRepository = null)
    {
        $registryMock = $this->getMock(RegistryInterface::class);
        $registryMock
            ->expects($this->any())
            ->method('getRepository')
            ->willReturn($entityRepository)
        ;

        return $registryMock;
    }
}
