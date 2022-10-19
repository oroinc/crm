<?php

namespace Oro\Bundle\ChannelBundle\EventListener;

use Oro\Bundle\ChannelBundle\Async\Topic\AggregateLifetimeAverageTopic;
use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;

/**
 * Sends MQ message to aggregate an average lifetime value
 */
class TimezoneChangeListener
{
    /**
     * @var MessageProducerInterface
     */
    private $messageProducer;

    public function __construct(MessageProducerInterface $messageProducer)
    {
        $this->messageProducer = $messageProducer;
    }

    public function onConfigUpdate(ConfigUpdateEvent $event)
    {
        if (!$event->isChanged('oro_locale.timezone')) {
            return;
        }

        $this->messageProducer->send(
            AggregateLifetimeAverageTopic::getName(),
            [
                'force' => true,
            ]
        );
    }
}
