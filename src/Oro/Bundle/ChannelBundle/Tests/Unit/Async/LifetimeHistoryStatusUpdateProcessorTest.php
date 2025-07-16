<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Async;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ChannelBundle\Async\LifetimeHistoryStatusUpdateProcessor;
use Oro\Bundle\ChannelBundle\Entity\LifetimeValueHistory;
use Oro\Bundle\ChannelBundle\Entity\Repository\LifetimeHistoryRepository;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Transport\Message;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LifetimeHistoryStatusUpdateProcessorTest extends TestCase
{
    private SessionInterface&MockObject $session;
    private ManagerRegistry&MockObject $doctrine;
    private MessageProducerInterface&MockObject $messageProducer;
    private LifetimeHistoryStatusUpdateProcessor $processor;

    #[\Override]
    protected function setUp(): void
    {
        $this->session = $this->createMock(SessionInterface::class);
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->messageProducer = $this->createMock(MessageProducerInterface::class);

        $this->processor = new LifetimeHistoryStatusUpdateProcessor($this->doctrine);
    }

    public function testShouldMassStatusUpdate(): void
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

        $this->messageProducer->expects(self::never())
            ->method('send');

        $this->processor->process($message, $this->session);
    }
}
