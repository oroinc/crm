<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Event;

use OroCRM\Bundle\ChannelBundle\Event\ChannelChangeStatusEvent;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class ChannelChangeStatusEventTest extends ChannelEventAbstractTest
{

    public function testConstructorRequiresChannel()
    {
        $expectedException = $this->getExpectedExceptionCode();
        $this->setExpectedException($expectedException);

        $channel = new ChannelChangeStatusEvent(null);
    }

    public function testGetter()
    {
        $channel = new Channel();
        $event   = new ChannelChangeStatusEvent($channel);

        $this->assertSame($channel, $event->getChannel());
    }
}
