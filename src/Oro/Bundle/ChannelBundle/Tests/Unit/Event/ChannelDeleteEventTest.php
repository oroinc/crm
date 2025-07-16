<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Event;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Event\ChannelDeleteEvent;
use PHPUnit\Framework\TestCase;

class ChannelDeleteEventTest extends TestCase
{
    public function testGetter(): void
    {
        $channel = new Channel();
        $event   = new ChannelDeleteEvent($channel);

        $this->assertSame($channel, $event->getChannel());
    }
}
