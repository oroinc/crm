<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Manager;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Manager\DeleteManager;

class DeleteManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $em;

    /** @var DeleteManager */
    protected $deleteManager;

    /** @var Channel */
    protected $channel;

    protected function setUp()
    {
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->deleteManager = new DeleteManager($this->em);
        $this->channel = new Channel();
    }

    public function testDeleteChannelWithoutErrors()
    {
        $this->em->expects($this->any())
            ->method('remove')
            ->with($this->equalTo($this->channel));
        $this->em->expects($this->any())
            ->method('flush');

        $this->assertTrue($this->deleteManager->delete($this->channel));
    }
}
