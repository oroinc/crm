<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Event;

use OroCRM\Bundle\ChannelBundle\Event\ChannelChangeStatusEvent;

class ChannelChangeStatusEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testConstructorRequiresChannel()
    {
        new ChannelChangeStatusEvent(null);
    }

    public function testGetter()
    {
        $channel = $this->getMock('OroCRM\Bundle\ChannelBundle\Entity\Channel');
        $event   = new ChannelChangeStatusEvent($channel);

        $this->assertSame($channel, $event->getChannel());
    }
}
