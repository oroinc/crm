<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Event;

use OroCRM\Bundle\ChannelBundle\Event\ChannelDeleteEvent;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class ChannelDeleteEventTest extends ChannelEventAbstractTest
{
    public function testConstructorRequiresChannel()
    {
        $expectedException = $this->getExpectedExceptionCode();
        $this->setExpectedException($expectedException);

        new ChannelDeleteEvent(null);
    }

    public function testGetter()
    {
        $channel = new Channel();
        $event   = new ChannelDeleteEvent($channel);

        $this->assertSame($channel, $event->getChannel());
    }
}
