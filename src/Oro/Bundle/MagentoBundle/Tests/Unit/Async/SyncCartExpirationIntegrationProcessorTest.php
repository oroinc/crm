<?php
namespace Oro\Bundle\MagentoBundle\Tests\Unit\Async;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;
use Oro\Bundle\MagentoBundle\Async\SyncCartExpirationIntegrationProcessor;
use Oro\Bundle\MagentoBundle\Async\Topics;
use Oro\Bundle\MagentoBundle\Provider\CartExpirationProcessor;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Test\JobRunner;
use Oro\Component\MessageQueue\Transport\Null\NullMessage;
use Oro\Component\MessageQueue\Transport\Null\NullSession;
use Oro\Component\MessageQueue\Util\JSON;
use Oro\Component\Testing\ClassExtensionTrait;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

class SyncCartExpirationIntegrationProcessorTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementMessageProcessorInterface()
    {
        $this->assertClassImplements(MessageProcessorInterface::class, SyncCartExpirationIntegrationProcessor::class);
    }

    public function testShouldImplementTopicSubscriberInterface()
    {
        $this->assertClassImplements(TopicSubscriberInterface::class, SyncCartExpirationIntegrationProcessor::class);
    }

    public function testShouldSubscribeOnSyncCartExpirationIntegrationTopic()
    {
        $this->assertEquals(
            [Topics::SYNC_CART_EXPIRATION_INTEGRATION],
            SyncCartExpirationIntegrationProcessor::getSubscribedTopics()
        );
    }

    public function testCouldBeConstructedWithExpectedArguments()
    {
        new SyncCartExpirationIntegrationProcessor(
            $this->createRegistryStub(),
            $this->createSyncProcessorMock(),
            new JobRunner(),
            $this->createLoggerMock()
        );
    }

    public function testShouldLogAndRejectIfMessageBodyMissIntegrationId()
    {
        $message = new NullMessage();
        $message->setBody('[]');

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('critical')
            ->with('The message invalid. It must have integrationId set', ['message' => $message])
        ;

        $processor = new SyncCartExpirationIntegrationProcessor(
            $this->createRegistryStub(),
            $this->createSyncProcessorMock(),
            new JobRunner(),
            $logger
        );

        $status = $processor->process($message, new NullSession());

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage The malformed json given.
     */
    public function testThrowIfMessageBodyInvalidJson()
    {
        $processor = new SyncCartExpirationIntegrationProcessor(
            $this->createRegistryStub(),
            $this->createSyncProcessorMock(),
            new JobRunner(),
            $this->createLoggerMock()
        );

        $message = new NullMessage();
        $message->setBody('[}');

        $processor->process($message, new NullSession());
    }

    public function testShouldRejectMessageIfIntegrationNotExist()
    {
        $repositoryStub = $this->createChannelRepositoryStub(null);
        $registryStub = $this->createRegistryStub($repositoryStub);

        $message = new NullMessage();
        $message->setBody(JSON::encode(['integrationId' => 'theIntegrationId']));

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('critical')
            ->with('The channel should exist and be enabled: theIntegrationId', ['message' => $message])
        ;

        $processor = new SyncCartExpirationIntegrationProcessor(
            $registryStub,
            $this->createSyncProcessorMock(),
            new JobRunner(),
            $logger
        );


        $status = $processor->process($message, new NullSession());

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    public function testShouldRejectMessageIfIntegrationIsNotEnabled()
    {
        $integration = new Channel();
        $integration->setEnabled(false);

        $repositoryMock = $this->createChannelRepositoryStub($integration);
        $registryStub = $this->createRegistryStub($repositoryMock);

        $message = new NullMessage();
        $message->setBody(JSON::encode(['integrationId' => 'theIntegrationId']));

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('critical')
            ->with('The channel should exist and be enabled: theIntegrationId', ['message' => $message])
        ;

        $processor = new SyncCartExpirationIntegrationProcessor(
            $registryStub,
            $this->createSyncProcessorMock(),
            new JobRunner(),
            $logger
        );

        $status = $processor->process($message, new NullSession());

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    public function testShouldRejectIfIntegrationNotHaveCartConnector()
    {
        $integration = new Channel();
        $integration->setEnabled(true);
        $integration->setConnectors(['foo', 'bar']);

        $repositoryMock = $this->createChannelRepositoryStub($integration);
        $registryStub = $this->createRegistryStub($repositoryMock);

        $message = new NullMessage();
        $message->setBody(JSON::encode(['integrationId' => 'theIntegrationId']));

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('critical')
            ->with(
                'The channel should have cart in connectors: theIntegrationId',
                [
                    'message' => $message,
                    'channel' => $integration
                ]
            )
        ;

        $processor = new SyncCartExpirationIntegrationProcessor(
            $registryStub,
            $this->createSyncProcessorMock(),
            new JobRunner(),
            $logger
        );

        $status = $processor->process($message, new NullSession());

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    public function testShouldCallCartExpirationProcessorAndAckMessage()
    {
        $integration = new Channel();
        $integration->setEnabled(true);
        $integration->setConnectors(['cart']);

        $repositoryMock = $this->createChannelRepositoryStub($integration);
        $registryStub = $this->createRegistryStub($repositoryMock);

        $syncProcessorMock = $this->createSyncProcessorMock();
        $syncProcessorMock
            ->expects($this->once())
            ->method('process')
            ->with(self::identicalTo($integration))
        ;

        $processor = new SyncCartExpirationIntegrationProcessor(
            $registryStub,
            $syncProcessorMock,
            new JobRunner(),
            $this->createLoggerMock()
        );

        $message = new NullMessage();
        $message->setBody(JSON::encode(['integrationId' => 'theIntegrationId']));

        $status = $processor->process($message, new NullSession());

        $this->assertEquals(MessageProcessorInterface::ACK, $status);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ChannelRepository
     */
    private function createChannelRepositoryStub(Channel $channel = null)
    {
        $repositoryMock = $this->getMock(ChannelRepository::class, [], [], '', false);
        $repositoryMock
            ->expects(self::any())
            ->method('getOrLoadById')
            ->willReturn($channel)
        ;
        
        return $repositoryMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|RegistryInterface
     */
    private function createRegistryStub(ChannelRepository $channelRepository = null)
    {
        $registryMock = $this->getMock(RegistryInterface::class);
        $registryMock
            ->expects(self::any())
            ->method('getRepository')
            ->with(Channel::class)
            ->willReturn($channelRepository)
        ;

        return $registryMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|CartExpirationProcessor
     */
    private function createSyncProcessorMock()
    {
        return $this->getMock(CartExpirationProcessor::class, [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject | LoggerInterface
     */
    private function createLoggerMock()
    {
        return $this->getMock(LoggerInterface::class);
    }
}
