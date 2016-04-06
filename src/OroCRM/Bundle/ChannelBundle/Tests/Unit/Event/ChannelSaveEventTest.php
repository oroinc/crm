<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Event;

use OroCRM\Bundle\ChannelBundle\Event\ChannelSaveEvent;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class ChannelSaveEventTest extends ChannelEventAbstractTest
{
    public function testConstructorRequiresChannel()
    {
        if($this->getPhpVersion() < self::PHP_VERSION_7) {
            $this->setExpectedException('PHPUnit_Framework_Error');
        } else {
            $this->setExpectedException('TypeError');
        }

        new ChannelSaveEvent(null);
    }

    public function testGetter()
    {
        $channel = new Channel();
        $event   = new ChannelSaveEvent($channel);

        $this->assertSame($channel, $event->getChannel());
    }
}
