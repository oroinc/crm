<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Model;

use OroCRM\Bundle\ChannelBundle\Tests\Unit\Stubs\Model\StubChannelEntity;
use OroCRM\Bundle\ChannelBundle\Tests\Unit\Event\ChannelEventAbstractTest;

class ChannelEntityTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruction()
    {
        $stub = new StubChannelEntity();

        $this->assertTrue(method_exists($stub, 'setDataChannel'));
        $this->assertTrue(method_exists($stub, 'getDataChannel'));
    }

    public function testDataSet()
    {
        $stub = new StubChannelEntity();

        $this->assertNull($stub->getDataChannel());

        $channel = $this->getMock('OroCRM\Bundle\ChannelBundle\Entity\Channel');
        $stub->setDataChannel($channel);

        $this->assertSame($channel, $stub->getDataChannel());
        $this->assertAttributeSame($channel, 'dataChannel', $stub);
    }
}
