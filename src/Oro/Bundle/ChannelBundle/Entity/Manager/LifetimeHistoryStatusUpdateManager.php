<?php

namespace Oro\Bundle\ChannelBundle\Entity\Manager;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ChannelBundle\Async\Topic\LifetimeHistoryStatusUpdateTopic;
use Oro\Bundle\ChannelBundle\Entity\LifetimeValueHistory;
use Oro\Bundle\ChannelBundle\Entity\Repository\LifetimeHistoryRepository;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;

/**
 * Executes mass status update directly in CLI mode, otherwise send message to the queue
 */
class LifetimeHistoryStatusUpdateManager
{
    private ManagerRegistry $doctrine;
    private MessageProducerInterface $messageProducer;
    private bool $useQueue = true;

    public function __construct(ManagerRegistry $doctrine, MessageProducerInterface $messageProducer)
    {
        $this->doctrine = $doctrine;
        $this->messageProducer = $messageProducer;
    }

    public function setUseQueue(bool $useQueue): self
    {
        $this->useQueue = $useQueue;

        return $this;
    }

    public function massUpdate(array $records, int $status = LifetimeValueHistory::STATUS_OLD): void
    {
        if ($this->useQueue) {
            $this->messageProducer->send(
                LifetimeHistoryStatusUpdateTopic::getName(),
                LifetimeHistoryStatusUpdateTopic::createMessage($this->normalizeRecords($records), $status)
            );
        } else {
            $this->getLifetimeRepository()->massStatusUpdate($this->normalizeRecords($records), $status);
        }
    }

    private function normalizeRecords(array $records): array
    {
        array_walk_recursive($records, function (&$elem) {
            if (\is_object($elem)) {
                $elem = $elem->getId();
            }
        });

        return $records;
    }

    private function getLifetimeRepository(): LifetimeHistoryRepository
    {
        return $this->doctrine->getRepository(LifetimeValueHistory::class);
    }
}
