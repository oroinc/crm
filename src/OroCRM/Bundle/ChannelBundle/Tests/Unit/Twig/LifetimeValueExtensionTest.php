<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Twig;

use OroCRM\Bundle\ChannelBundle\Provider\Lifetime\AmountProvider;
use OroCRM\Bundle\ChannelBundle\Twig\LifetimeValueExtension;

class LifetimeValueExtensionTest extends \PHPUnit_Framework_TestCase
{
    /** @var AmountProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $provider;

    /** @var LifetimeValueExtension */
    protected $extension;

    public function setUp()
    {
        $this->provider = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Provider\Lifetime\AmountProvider')
            ->disableOriginalConstructor()->getMock();

        $this->extension = new LifetimeValueExtension($this->provider);
    }

    public function tearDown()
    {
        unset($this->extension, $this->provider);
    }

    public function testGetLifetimeValue()
    {
        $expectedResult = 12.33;
        $account        = $this->getMock('OroCRM\Bundle\AccountBundle\Entity\Account');
        $channel        = $this->getMock('OroCRM\Bundle\ChannelBundle\Entity\Channel');

        $this->provider->expects($this->once())->method('getAccountLifeTimeValue')
            ->with($this->equalTo($account), $this->equalTo($channel))
            ->will($this->returnValue($expectedResult));

        $this->assertSame($expectedResult, $this->extension->getLifetimeValue($account, $channel));
    }

    public function testGetName()
    {
        $this->assertEquals($this->extension->getName(), 'orocrm_channel_lifetime_value');
    }

    public function testGetFunctions()
    {
        $this->assertArrayHasKey('orocrm_channel_account_lifetime', $this->extension->getFunctions());
    }
}
