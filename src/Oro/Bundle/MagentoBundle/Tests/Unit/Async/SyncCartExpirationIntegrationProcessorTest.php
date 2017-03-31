<?php
namespace Oro\Bundle\MagentoBundle\Tests\Unit\Async;

use Psr\Log\LoggerInterface;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository as IntegrationRepository;
use Oro\Bundle\MagentoBundle\Async\SyncCartExpirationIntegrationProcessor;
use Oro\Bundle\MagentoBundle\Async\Topics;
use Oro\Bundle\MagentoBundle\Provider\CartExpirationProcessor;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Test\JobRunner;
use Oro\Component\MessageQueue\Transport\Null\NullMessage;
use Oro\Component\MessageQueue\Transport\Null\NullSession;
use Oro\Component\MessageQueue\Util\JSON;
use Oro\Component\Testing\ClassExtensionTrait;

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
            $this->createTokenStorageMock(),
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
            $this->createTokenStorageMock(),
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
            $this->createTokenStorageMock(),
            $this->createLoggerMock()
        );

        $message = new NullMessage();
        $message->setBody('[}');

        $processor->process($message, new NullSession());
    }

    public function testShouldRejectMessageIfIntegrationNotExist()
    {
        $repositoryStub = $this->createIntegrationRepositoryStub(null);
        $registryStub = $this->createRegistryStub($repositoryStub);

        $message = new NullMessage();
        $message->setBody(JSON::encode(['integrationId' => 'theIntegrationId']));

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('error')
            ->with('The integration should exist and be enabled: theIntegrationId', ['message' => $message])
        ;

        $processor = new SyncCartExpirationIntegrationProcessor(
            $registryStub,
            $this->createSyncProcessorMock(),
            new JobRunner(),
            $this->createTokenStorageMock(),
            $logger
        );


        $status = $processor->process($message, new NullSession());

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    public function testShouldRejectMessageIfIntegrationIsNotEnabled()
    {
        $integration = new Integration();
        $integration->setEnabled(false);

        $repositoryMock = $this->createIntegrationRepositoryStub($integration);
        $registryStub = $this->createRegistryStub($repositoryMock);

        $message = new NullMessage();
        $message->setBody(JSON::encode(['integrationId' => 'theIntegrationId']));

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('error')
            ->with('The integration should exist and be enabled: theIntegrationId', ['message' => $message])
        ;

        $processor = new SyncCartExpirationIntegrationProcessor(
            $registryStub,
            $this->createSyncProcessorMock(),
            new JobRunner(),
            $this->createTokenStorageMock(),
            $logger
        );

        $status = $processor->process($message, new NullSession());

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    public function testShouldRejectIfIntegrationNotHaveCartConnector()
    {
        $integration = new Integration();
        $integration->setEnabled(true);
        $integration->setConnectors(['foo', 'bar']);

        $repositoryMock = $this->createIntegrationRepositoryStub($integration);
        $registryStub = $this->createRegistryStub($repositoryMock);

        $message = new NullMessage();
        $message->setBody(JSON::encode(['integrationId' => 'theIntegrationId']));

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('error')
            ->with(
                'The integration should have cart in connectors: theIntegrationId',
                [
                    'message' => $message,
                    'integration' => $integration
                ]
            )
        ;

        $processor = new SyncCartExpirationIntegrationProcessor(
            $registryStub,
            $this->createSyncProcessorMock(),
            new JobRunner(),
            $this->createTokenStorageMock(),
            $logger
        );

        $status = $processor->process($message, new NullSession());

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    public function testShouldCallCartExpirationProcessorAndAckMessage()
    {
        $integration = new Integration();
        $integration->setEnabled(true);
        $integration->setConnectors(['cart']);
        $integration->setOrganization(new Organization());

        $repositoryMock = $this->createIntegrationRepositoryStub($integration);
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
            $this->createTokenStorageMock(),
            $this->createLoggerMock()
        );

        $message = new NullMessage();
        $message->setBody(JSON::encode(['integrationId' => 'theIntegrationId']));

        $status = $processor->process($message, new NullSession());

        $this->assertEquals(MessageProcessorInterface::ACK, $status);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|IntegrationRepository
     */
    private function createIntegrationRepositoryStub(Integration $integration = null)
    {
        $repositoryMock = $this->createMock(IntegrationRepository::class);
        $repositoryMock
            ->expects(self::any())
            ->method('getOrLoadById')
            ->willReturn($integration)
        ;
        
        return $repositoryMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|RegistryInterface
     */
    private function createRegistryStub(IntegrationRepository $integrationRepository = null)
    {
        $registryMock = $this->createMock(RegistryInterface::class);
        $registryMock
            ->expects(self::any())
            ->method('getRepository')
            ->with(Integration::class)
            ->willReturn($integrationRepository)
        ;

        return $registryMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|CartExpirationProcessor
     */
    private function createSyncProcessorMock()
    {
        return $this->createMock(CartExpirationProcessor::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject | LoggerInterface
     */
    private function createLoggerMock()
    {
        return $this->createMock(LoggerInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject | TokenStorageInterface
     */
    private function createTokenStorageMock()
    {
        return $this->createMock(TokenStorageInterface::class);
    }
}
