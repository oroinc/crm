<?php
namespace OroCRM\Bundle\ChannelBundle\Tests\Functional\EventListener;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use OroCRM\Bundle\ChannelBundle\EventListener\TimezoneChangeListener;

class TimezoneChangeListenerTest extends WebTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initClient();
    }

    public function testCouldBeGetFromContainerAsService()
    {
        $service = $this->getContainer()->get('orocrm_channel.event_listener.timezone_change');

        $this->assertInstanceOf(TimezoneChangeListener::class, $service);
    }
}
