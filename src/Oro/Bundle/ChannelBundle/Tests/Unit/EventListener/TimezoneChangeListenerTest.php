<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\EventListener;

use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Bundle\ChannelBundle\Async\Topics;
use Oro\Bundle\ChannelBundle\EventListener\TimezoneChangeListener;
use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;
use Oro\Bundle\MessageQueueBundle\Test\Unit\MessageQueueExtension;

class TimezoneChangeListenerTest extends \PHPUnit_Framework_TestCase
{
    use MessageQueueExtension;

    public function testWasNotChanged()
    {
        $listener = new TimezoneChangeListener(self::getMessageProducer());

        $listener->onConfigUpdate(new ConfigUpdateEvent([]));

        self::assertMessagesEmpty(Topics::AGGREGATE_LIFETIME_AVERAGE);
    }

    public function testSuccessChange()
    {
        $listener = new TimezoneChangeListener(self::getMessageProducer());

        $listener->onConfigUpdate(new ConfigUpdateEvent(['oro_locale.timezone' => ['old' => 1, 'new' => 2]]));

        self::assertMessageSent(
            Topics::AGGREGATE_LIFETIME_AVERAGE,
            new Message(['force' => true], MessagePriority::VERY_LOW)
        );
    }
}
