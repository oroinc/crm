<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\EventListener;

use Oro\Bundle\ChannelBundle\Async\Topic\AggregateLifetimeAverageTopic;
use Oro\Bundle\ChannelBundle\EventListener\TimezoneChangeListener;
use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;
use Oro\Bundle\MessageQueueBundle\Test\Unit\MessageQueueExtension;

class TimezoneChangeListenerTest extends \PHPUnit\Framework\TestCase
{
    use MessageQueueExtension;

    public function testWasNotChanged(): void
    {
        $listener = new TimezoneChangeListener(self::getMessageProducer());

        $listener->onConfigUpdate(new ConfigUpdateEvent([]));

        self::assertMessagesEmpty(AggregateLifetimeAverageTopic::getName());
    }

    public function testSuccessChange(): void
    {
        $listener = new TimezoneChangeListener(self::getMessageProducer());

        $listener->onConfigUpdate(new ConfigUpdateEvent(['oro_locale.timezone' => ['old' => 1, 'new' => 2]]));

        self::assertMessageSent(
            AggregateLifetimeAverageTopic::getName(),
            ['force' => true]
        );
    }
}
