<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Async;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository as IntegrationRepository;
use Oro\Bundle\MagentoBundle\Async\SyncCartExpirationIntegrationProcessor;
use Oro\Bundle\MagentoBundle\Async\Topics;
use Oro\Bundle\MagentoBundle\Exception\ExtensionRequiredException;
use Oro\Bundle\MagentoBundle\Provider\CartExpirationProcessor;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Test\JobRunner;
use Oro\Component\MessageQueue\Transport\Message;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use Oro\Component\Testing\ClassExtensionTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class SyncCartExpirationIntegrationProcessorTest extends \PHPUnit\Framework\TestCase
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
        $message = new Message();
        $message->setBody('[]');

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('critical')
            ->with('The message invalid. It must have integrationId set')
        ;

        $processor = new SyncCartExpirationIntegrationProcessor(
            $this->createRegistryStub(),
            $this->createSyncProcessorMock(),
            new JobRunner(),
            $this->createTokenStorageMock(),
            $logger
        );

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, new $session);

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    public function testThrowIfMessageBodyInvalidJson()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The malformed json given.');

        $processor = new SyncCartExpirationIntegrationProcessor(
            $this->createRegistryStub(),
            $this->createSyncProcessorMock(),
            new JobRunner(),
            $this->createTokenStorageMock(),
            $this->createLoggerMock()
        );

        $message = new Message();
        $message->setBody('[}');

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $processor->process($message, $session);
    }

    public function testShouldRejectMessageIfIntegrationNotExist()
    {
        $repositoryStub = $this->createIntegrationRepositoryStub(null);
        $registryStub = $this->createRegistryStub($repositoryStub);

        $message = new Message();
        $message->setBody(JSON::encode(['integrationId' => 'theIntegrationId']));

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('error')
            ->with('The integration should exist and be enabled: theIntegrationId')
        ;

        $processor = new SyncCartExpirationIntegrationProcessor(
            $registryStub,
            $this->createSyncProcessorMock(),
            new JobRunner(),
            $this->createTokenStorageMock(),
            $logger
        );

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    public function testShouldRejectMessageIfIntegrationIsNotEnabled()
    {
        $integration = new Integration();
        $integration->setEnabled(false);

        $repositoryMock = $this->createIntegrationRepositoryStub($integration);
        $registryStub = $this->createRegistryStub($repositoryMock);

        $message = new Message();
        $message->setBody(JSON::encode(['integrationId' => 'theIntegrationId']));

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('error')
            ->with('The integration should exist and be enabled: theIntegrationId')
        ;

        $processor = new SyncCartExpirationIntegrationProcessor(
            $registryStub,
            $this->createSyncProcessorMock(),
            new JobRunner(),
            $this->createTokenStorageMock(),
            $logger
        );

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    public function testShouldRejectIfIntegrationNotHaveCartConnector()
    {
        $integration = new Integration();
        $integration->setEnabled(true);
        $integration->setConnectors(['foo', 'bar']);

        $repositoryMock = $this->createIntegrationRepositoryStub($integration);
        $registryStub = $this->createRegistryStub($repositoryMock);

        $message = new Message();
        $message->setBody(JSON::encode(['integrationId' => 'theIntegrationId']));

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('error')
            ->with(
                'The integration should have cart in connectors: theIntegrationId',
                [
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

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    public function testShouldRejectIfBridgeExtensionNotEnabled()
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
            ->willThrowException(new ExtensionRequiredException);

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('warning')
            ->with(
                (new ExtensionRequiredException)->getMessage(),
                ['exception' => new ExtensionRequiredException]
            )
        ;

        $processor = new SyncCartExpirationIntegrationProcessor(
            $registryStub,
            $syncProcessorMock,
            new JobRunner(),
            $this->createTokenStorageMock(),
            $logger
        );

        $message = new Message();
        $message->setBody(JSON::encode(['integrationId' => 'theIntegrationId']));

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

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

        $message = new Message();
        $message->setBody(JSON::encode(['integrationId' => 'theIntegrationId']));

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::ACK, $status);
    }

    /**
     * @param Integration|null $integration
     * @return \PHPUnit\Framework\MockObject\MockObject|IntegrationRepository
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
     * @param IntegrationRepository|null $integrationRepository
     * @return \PHPUnit\Framework\MockObject\MockObject|ManagerRegistry
     */
    private function createRegistryStub(IntegrationRepository $integrationRepository = null)
    {
        $registryMock = $this->createMock(ManagerRegistry::class);
        $registryMock
            ->expects(self::any())
            ->method('getRepository')
            ->with(Integration::class)
            ->willReturn($integrationRepository)
        ;

        return $registryMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|CartExpirationProcessor
     */
    private function createSyncProcessorMock()
    {
        return $this->createMock(CartExpirationProcessor::class);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject | LoggerInterface
     */
    private function createLoggerMock()
    {
        return $this->createMock(LoggerInterface::class);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject | TokenStorageInterface
     */
    private function createTokenStorageMock()
    {
        return $this->createMock(TokenStorageInterface::class);
    }
}
