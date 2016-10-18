<?php

namespace Oro\Bundle\ChannelBundle\EventListener;

use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Bundle\ChannelBundle\Async\Topics;

class TimezoneChangeListener
{
    /**
     * @var MessageProducerInterface
     */
    private $messageProducer;

    /**
     * @param MessageProducerInterface $messageProducer
     */
    public function __construct(MessageProducerInterface $messageProducer)
    {
        $this->messageProducer = $messageProducer;
    }

    /**
     * @param ConfigUpdateEvent $event
     */
    public function onConfigUpdate(ConfigUpdateEvent $event)
    {
        if (!$event->isChanged('oro_locale.timezone')) {
            return;
        }

        $message = new Message();
        $message->setPriority(MessagePriority::VERY_LOW);
        $message->setBody([
            'force' => true,
        ]);

        $this->messageProducer->send(Topics::AGGREGATE_LIFETIME_AVERAGE, $message);
    }
}
