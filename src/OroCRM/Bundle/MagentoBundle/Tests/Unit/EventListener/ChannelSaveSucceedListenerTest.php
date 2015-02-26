<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\EventListener;

use OroCRM\Bundle\ChannelBundle\Tests\Unit\EventListener\ChannelSaveSucceedListenerTest as BaseTestCase;
use OroCRM\Bundle\MagentoBundle\EventListener\ChannelSaveSucceedListener;
use OroCRM\Bundle\MagentoBundle\Provider\ChannelType;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class ChannelSaveSucceedListenerTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->entity->setChannelType(ChannelType::TYPE);
    }

    /**
     * @return ChannelSaveSucceedListener
     */
    protected function getListener()
    {
        return new ChannelSaveSucceedListener($this->settingProvider, $this->registry);
    }

    public function assertConnectors()
    {
        $this->assertEquals(
            $this->integration->getConnectors(),
            ['TestConnector1_initial', 'TestConnector2_initial', 'TestConnector1', 'TestConnector2']
        );
    }

    public function testNonMagentoChannel()
    {
        $channel = new Channel();
        $channel->setChannelType('any');
        $event =$this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Event\ChannelSaveEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getChannel')
            ->will($this->returnValue($channel));
        $event->expects($this->never())
            ->method('getDataSource');

        $this->em->expects($this->never())
            ->method($this->anything());

        $this->getListener()->onChannelSucceedSave($event);
    }
}
