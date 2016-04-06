<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Model;

use OroCRM\Bundle\ChannelBundle\Tests\Unit\Stubs\Model\StubChannelEntity;
use OroCRM\Bundle\ChannelBundle\Tests\Unit\Event\ChannelEventAbstractTest;

class ChannelEntityTraitTest extends ChannelEventAbstractTest
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
    
    public function testSetterHasTypeHint()
    {
        if($this->getPhpVersion() < self::PHP_VERSION_7) {
            $this->setExpectedException('PHPUnit_Framework_Error');
        } else {
            $this->setExpectedException('TypeError');
        }

        $stub = new StubChannelEntity();
        $stub->setDataChannel('testString');
    }
}
