<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Event;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Event\ChannelSaveEvent;
use PHPUnit\Framework\TestCase;

class ChannelSaveEventTest extends TestCase
{
    public function testGetter(): void
    {
        $channel = new Channel();
        $event   = new ChannelSaveEvent($channel);

        $this->assertSame($channel, $event->getChannel());
    }
}
