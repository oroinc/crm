<?php
namespace Oro\Bundle\AnalyticsBundle\Async;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Bundle\AnalyticsBundle\Service\ScheduleCalculateAnalyticsService;
use Oro\Bundle\ChannelBundle\Entity\Channel;

class CalculateAllChannelsAnalyticsProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @var ScheduleCalculateAnalyticsService
     */
    private $scheduleCalculateAnalyticsService;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param ScheduleCalculateAnalyticsService $scheduleCalculateAnalyticsService
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        ScheduleCalculateAnalyticsService $scheduleCalculateAnalyticsService
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->scheduleCalculateAnalyticsService = $scheduleCalculateAnalyticsService;
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
            $this->scheduleCalculateAnalyticsService->scheduleForChannel($channel->getId());
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
