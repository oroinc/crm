<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Event;

use OroCRM\Bundle\ChannelBundle\Event\ChannelSaveEvent;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class ChannelSaveEventTest extends ChannelEventAbstractTest
{
    public function testConstructorRequiresChannel()
    {
        $expectedException = $this->getExpectedExceptionCode();
        $this->setExpectedException($expectedException);

        new ChannelSaveEvent(null);
    }

    public function testGetter()
    {
        $channel = new Channel();
        $event   = new ChannelSaveEvent($channel);

        $this->assertSame($channel, $event->getChannel());
    }
}
