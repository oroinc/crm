<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\EventListener;

use Oro\Bundle\ChannelBundle\Async\Topic\AggregateLifetimeAverageTopic;
use Oro\Bundle\ChannelBundle\EventListener\TimezoneChangeListener;
use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;
use Oro\Bundle\MessageQueueBundle\Test\Unit\MessageQueueExtension;
use PHPUnit\Framework\TestCase;

class TimezoneChangeListenerTest extends TestCase
{
    use MessageQueueExtension;

    public function testWasNotChanged(): void
    {
        $listener = new TimezoneChangeListener(self::getMessageProducer());

        $listener->onConfigUpdate(new ConfigUpdateEvent([], 'global', 0));

        self::assertMessagesEmpty(AggregateLifetimeAverageTopic::getName());
    }

    public function testSuccessChange(): void
    {
        $listener = new TimezoneChangeListener(self::getMessageProducer());

        $listener->onConfigUpdate(
            new ConfigUpdateEvent(['oro_locale.timezone' => ['old' => 1, 'new' => 2]], 'global', 0)
        );

        self::assertMessageSent(
            AggregateLifetimeAverageTopic::getName(),
            ['force' => true]
        );
    }
}
