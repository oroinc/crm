<?php
namespace Oro\Bundle\AnalyticsBundle\Async;

use Oro\Bundle\AnalyticsBundle\Builder\AnalyticsBuilder;
use Oro\Bundle\AnalyticsBundle\Model\AnalyticsAwareInterface;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use Psr\Log\LoggerInterface;

class CalculateChannelAnalyticsProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @var AnalyticsBuilder
     */
    private $analyticsBuilder;

    /**
     * @var JobRunner
     */
    private $jobRunner;

    /**
     * @var LoggerInterface
     */
    private $logger;

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
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $body = JSON::decode($message->getBody());
        $body = array_replace_recursive([
            'channel_id' => null,
            'customer_ids' => [],
        ], $body);

        if (false == $body['channel_id']) {
            $this->logger->critical('The message invalid. It must have channel_id set');

            return self::REJECT;
        }

        $jobName = 'oro_analytics:calculate_channel_analytics:'.$body['channel_id'];
        $ownerId = $message->getMessageId();

        $em = $this->doctrineHelper->getEntityManager(Channel::class);

        /** @var Channel $channel */
        $channel = $em->find(Channel::class, $body['channel_id']);
        if (! $channel) {
            $this->logger->error(sprintf('Channel not found: %s', $body['channel_id']));

            return self::REJECT;
        }
        if (Channel::STATUS_ACTIVE != $channel->getStatus()) {
            $this->logger->error(sprintf('Channel not active: %s', $body['channel_id']));

            return self::REJECT;
        }
        if (false == is_a($channel->getCustomerIdentity(), AnalyticsAwareInterface::class, true)) {
            $this->logger->error(
                sprintf('Channel is not supposed to calculate analytics: %s', $body['channel_id'])
            );

            return self::REJECT;
        }

        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $result = $this->jobRunner->runUnique($ownerId, $jobName, function () use ($channel, $body) {
            $this->analyticsBuilder->build($channel, $body['customer_ids']);

            return true;
        });

        return $result ? self::ACK : self::REJECT;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return [Topics::CALCULATE_CHANNEL_ANALYTICS];
    }
}
