<?php

namespace Oro\Bundle\ChannelBundle\Async;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ChannelBundle\Async\Topic\LifetimeHistoryStatusUpdateTopic;
use Oro\Bundle\ChannelBundle\Entity\LifetimeValueHistory;
use Oro\Bundle\ChannelBundle\Entity\Repository\LifetimeHistoryRepository;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;

/**
 * Runs mass status update of history entries
 */
class LifetimeHistoryStatusUpdateProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function process(MessageInterface $message, SessionInterface $session): string
    {
        $messageBody = $message->getBody();
        $this->getLifetimeRepository()->massStatusUpdate(
            $messageBody[LifetimeHistoryStatusUpdateTopic::RECORDS_FIELD],
            $messageBody[LifetimeHistoryStatusUpdateTopic::STATUS_FIELD] ?? LifetimeValueHistory::STATUS_OLD
        );

        return self::ACK;
    }

    private function getLifetimeRepository(): LifetimeHistoryRepository
    {
        return $this->doctrine->getRepository(LifetimeValueHistory::class);
    }

    public static function getSubscribedTopics(): array
    {
        return [LifetimeHistoryStatusUpdateTopic::getName()];
    }
}
