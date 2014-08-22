<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Model;

use OroCRM\Bundle\ChannelBundle\Tests\Unit\Stubs\Model\StubChannelEntity;

class ChannelEntityTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruction()
    {
        $stub = new StubChannelEntity();

        $this->assertTrue(method_exists($stub, 'setChannel'));
        $this->assertTrue(method_exists($stub, 'getChannel'));
    }

    public function testDataSet()
    {
        $stub = new StubChannelEntity();

        $this->assertNull($stub->getChannel());

        $channel = $this->getMock('OroCRM\Bundle\ChannelBundle\Entity\Channel');
        $stub->setChannel($channel);

        $this->assertSame($channel, $stub->getChannel());
        $this->assertAttributeSame($channel, 'channel', $stub);
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testSetterHasTypeHint()
    {
        $stub = new StubChannelEntity();
        $stub->setChannel('testString');
    }
}
