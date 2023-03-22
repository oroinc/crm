<?php
namespace Oro\Bundle\AnalyticsBundle\Async;

use Oro\Bundle\AnalyticsBundle\Async\Topic\CalculateChannelAnalyticsTopic;
use Oro\Bundle\AnalyticsBundle\Builder\AnalyticsBuilder;
use Oro\Bundle\AnalyticsBundle\Model\AnalyticsAwareInterface;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Psr\Log\LoggerInterface;

/**
 * Calculates analytics for specified channel
 */
class CalculateChannelAnalyticsProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    private DoctrineHelper $doctrineHelper;

    private AnalyticsBuilder $analyticsBuilder;

    private JobRunner $jobRunner;

    private LoggerInterface $logger;

    public function __construct(
        DoctrineHelper $doctrineHelper,
        AnalyticsBuilder $analyticsBuilder,
        JobRunner $jobRunner,
        LoggerInterface $logger
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->jobRunner = $jobRunner;
        $this->analyticsBuilder = $analyticsBuilder;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session): string
    {
        $messageBody = $message->getBody();

        $entityManager = $this->doctrineHelper->getEntityManager(Channel::class);

        /** @var Channel $channel */
        $channel = $entityManager?->find(Channel::class, $messageBody['channel_id']);
        if (!$channel) {
            $this->logger->error(sprintf('Channel not found: %s', $messageBody['channel_id']));

            return self::REJECT;
        }
        if (Channel::STATUS_ACTIVE !== $channel->getStatus()) {
            $this->logger->error(sprintf('Channel not active: %s', $messageBody['channel_id']));

            return self::REJECT;
        }

        if (false === is_a($channel->getCustomerIdentity(), AnalyticsAwareInterface::class, true)) {
            $this->logger->error(
                sprintf('Channel is not supposed to calculate analytics: %s', $messageBody['channel_id'])
            );

            return self::REJECT;
        }

        $entityManager?->getConnection()?->getConfiguration()?->setSQLLogger(null);

        $result = $this->jobRunner->runUniqueByMessage($message, function () use ($channel, $messageBody) {
            $this->analyticsBuilder->build($channel, $messageBody['customer_ids']);

            return true;
        });

        return $result ? self::ACK : self::REJECT;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics(): array
    {
        return [CalculateChannelAnalyticsTopic::getName()];
    }
}
