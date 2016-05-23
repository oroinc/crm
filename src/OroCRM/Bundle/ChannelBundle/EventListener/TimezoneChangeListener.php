<?php

namespace OroCRM\Bundle\ChannelBundle\EventListener;

use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use OroCRM\Bundle\ChannelBundle\Async\Topics;

use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;

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

        $this->messageProducer->send(Topics::AGGREGATE_LIFETIME_AVERAGE, [
            'force' => true,
        ], MessagePriority::VERY_LOW);
    }
}
