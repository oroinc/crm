<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\EventListener;

use Oro\Bundle\ChannelBundle\Async\Topics;
use Oro\Bundle\ChannelBundle\EventListener\TimezoneChangeListener;
use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;
use Oro\Bundle\MessageQueueBundle\Test\Unit\MessageQueueExtension;
use Oro\Component\MessageQueue\Client\MessagePriority;

class TimezoneChangeListenerTest extends \PHPUnit\Framework\TestCase
{
    use MessageQueueExtension;

    public function testWasNotChanged(): void
    {
        $listener = new TimezoneChangeListener(self::getMessageProducer());

        $listener->onConfigUpdate(new ConfigUpdateEvent([]));

        self::assertMessagesEmpty(Topics::AGGREGATE_LIFETIME_AVERAGE);
    }

    public function testSuccessChange(): void
    {
        $listener = new TimezoneChangeListener(self::getMessageProducer());

        $listener->onConfigUpdate(new ConfigUpdateEvent(['oro_locale.timezone' => ['old' => 1, 'new' => 2]]));

        self::assertMessageSent(
            Topics::AGGREGATE_LIFETIME_AVERAGE,
            ['force' => true]
        );
        self::assertMessageSentWithPriority(Topics::AGGREGATE_LIFETIME_AVERAGE, MessagePriority::VERY_LOW);
    }
}
