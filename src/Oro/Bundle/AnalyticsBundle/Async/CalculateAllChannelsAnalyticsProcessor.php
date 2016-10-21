<?php
namespace Oro\Bundle\AnalyticsBundle\Async;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Bundle\AnalyticsBundle\Service\CalculateAnalyticsScheduler;
use Oro\Bundle\ChannelBundle\Entity\Channel;

class CalculateAllChannelsAnalyticsProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @var CalculateAnalyticsScheduler
     */
    private $calculateAnalyticsScheduler;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param CalculateAnalyticsScheduler $calculateAnalyticsScheduler
     */
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
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $qb = $this->doctrineHelper->getEntityRepository(Channel::class)->createQueryBuilder('c');

        $qb->orderBy($qb->expr()->asc('c.id'));
        $qb->andWhere('c.status = :status');
        $qb->setParameter('status', Channel::STATUS_ACTIVE);

        /** @var Channel[] $channels */
        $channels = new BufferedQueryResultIterator($qb);

        foreach ($channels as $channel) {
            $this->calculateAnalyticsScheduler->scheduleForChannel($channel->getId());
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return [Topics::CALCULATE_ALL_CHANNELS_ANALYTICS];
    }
}
