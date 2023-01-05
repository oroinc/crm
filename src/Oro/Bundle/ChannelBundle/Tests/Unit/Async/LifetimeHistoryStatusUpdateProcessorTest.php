<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Async;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ChannelBundle\Async\LifetimeHistoryStatusUpdateProcessor;
use Oro\Bundle\ChannelBundle\Entity\LifetimeValueHistory;
use Oro\Bundle\ChannelBundle\Entity\Repository\LifetimeHistoryRepository;
use Oro\Bundle\ChannelBundle\Provider\StateProvider;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Transport\Message;
use Oro\Component\MessageQueue\Transport\SessionInterface;

class LifetimeHistoryStatusUpdateProcessorTest extends \PHPUnit\Framework\TestCase
{
    private SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session;
    private ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject $doctrine;
    private LifetimeHistoryStatusUpdateProcessor $processor;
    private MessageProducerInterface $messageProducer;

    protected function setUp(): void
    {
        $this->stateProvider = $this->createMock(StateProvider::class);
        $this->entityManager = $this->createMock(EntityManager::class);
        $this->session = $this->createMock(SessionInterface::class);
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->messageProducer = $this->createMock(MessageProducerInterface::class);

        $this->processor = new LifetimeHistoryStatusUpdateProcessor($this->doctrine);
    }

    public function testShouldMassStatusUpdate()
    {
        $message = new Message();
        $message->setBody([
            'records' => [
                [1, null, 2],
                [5, 6, 7],
            ],
            'status' => LifetimeValueHistory::STATUS_NEW,
        ]);

        $lifetimeRepository = $this->createMock(LifetimeHistoryRepository::class);

        $this->doctrine->expects(self::once())
            ->method('getRepository')
            ->with(LifetimeValueHistory::class)
            ->willReturn($lifetimeRepository);

        $lifetimeRepository->expects(self::once())
            ->method('massStatusUpdate')
            ->with(
                [
                    [1, null, 2],
                    [5, 6, 7],
                ],
                LifetimeValueHistory::STATUS_NEW,
            );

        $this->messageProducer->expects(self::never())->method('send');

        $this->processor->process($message, $this->session);
    }
}
