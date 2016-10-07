<?php
namespace OroCRM\Bundle\AnalyticsBundle\Async;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use OroCRM\Bundle\AnalyticsBundle\Builder\AnalyticsBuilder;
use OroCRM\Bundle\AnalyticsBundle\Model\AnalyticsAwareInterface;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

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
     * @param DoctrineHelper $doctrineHelper
     * @param AnalyticsBuilder $analyticsBuilder
     * @param JobRunner $jobRunner
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        AnalyticsBuilder $analyticsBuilder,
        JobRunner $jobRunner
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->jobRunner = $jobRunner;
        $this->analyticsBuilder = $analyticsBuilder;
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
            throw new \LogicException('The message invalid. It must have channel_id set');
        }

        $jobName = 'orocrm_analytics:calculate_channel_analytics:'.$body['channel_id'];
        $ownerId = $message->getMessageId();

        $result = $this->jobRunner->runUnique($ownerId, $jobName, function () use ($body) {
            $em = $this->doctrineHelper->getEntityManager(Channel::class);

            /** @var Channel $channel */
            $channel = $em->find(Channel::class, $body['channel_id']);
            if (false == $channel) {
                return false;
            }
            if (Channel::STATUS_ACTIVE != $channel->getStatus()) {
                return false;
            }
            if (false == is_a($channel->getCustomerIdentity(), AnalyticsAwareInterface::class, true)) {
                return false;
            }

            $em->getConnection()->getConfiguration()->setSQLLogger(null);

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
