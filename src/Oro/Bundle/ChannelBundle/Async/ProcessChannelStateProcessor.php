<?php
namespace Oro\Bundle\ChannelBundle\Async;

use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Bundle\ChannelBundle\Provider\StateProvider;

class ProcessChannelStateProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    /**
     * @var StateProvider
     */
    private $stateProvider;

    /**
     * @param StateProvider $stateProvider
     */
    public function __construct(StateProvider $stateProvider)
    {
        $this->stateProvider = $stateProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $this->stateProvider->processChannelChange();
        
        return self::ACK;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return [Topics::CHANNEL_STATUS_CHANGED];
    }
}
