<?php
namespace Oro\Bundle\AnalyticsBundle\Async;

use Oro\Bundle\AnalyticsBundle\Async\Topic\CalculateAllChannelsAnalyticsTopic;
use Oro\Bundle\AnalyticsBundle\Model\AnalyticsAwareInterface;
use Oro\Bundle\AnalyticsBundle\Service\CalculateAnalyticsScheduler;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;

/**
 * Calculates analytics for all channels
 */
class CalculateAllChannelsAnalyticsProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    private DoctrineHelper $doctrineHelper;

    private CalculateAnalyticsScheduler $calculateAnalyticsScheduler;

    public function __construct(
        DoctrineHelper $doctrineHelper,
        CalculateAnalyticsScheduler $calculateAnalyticsScheduler
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->calculateAnalyticsScheduler = $calculateAnalyticsScheduler;
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session): string
    {
        $qb = $this->doctrineHelper->getEntityRepository(Channel::class)->createQueryBuilder('c');

        $qb->orderBy($qb->expr()->asc('c.id'));
        $qb->andWhere('c.status = :status');
        $qb->setParameter('status', Channel::STATUS_ACTIVE);

        /** @var Channel[] $channels */
        $channels = new BufferedQueryResultIterator($qb);

        foreach ($channels as $channel) {
            // check if the channel's customer supports analytics.
            if (!is_a($channel->getCustomerIdentity(), AnalyticsAwareInterface::class, true)) {
                continue;
            }

            $this->calculateAnalyticsScheduler->scheduleForChannel($channel->getId());
        }

        return self::ACK;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics(): array
    {
        return [CalculateAllChannelsAnalyticsTopic::getName()];
    }
}
