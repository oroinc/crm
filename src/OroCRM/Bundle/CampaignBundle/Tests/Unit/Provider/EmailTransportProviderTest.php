<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Unit\Provider;

use OroCRM\Bundle\CampaignBundle\Provider\EmailTransportProvider;

class EmailTransportProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProviderMethods()
    {
        $provider = new EmailTransportProvider();
        $name = 'test';
        $transport = $this->getMock('OroCRM\Bundle\CampaignBundle\Transport\TransportInterface');
        $transport->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($name));

        $this->assertEmpty($provider->getTransports());
        $this->assertFalse($provider->hasTransport($name));

        $provider->addTransport($transport);
        $this->assertTrue($provider->hasTransport($name));
        $this->assertCount(1, $provider->getTransports());
        $this->assertEquals($transport, $provider->getTransportByName($name));
    }

    public function testTransportIsVisibleInForm()
    {
        $provider = new EmailTransportProvider();
        $transport = $this->getMock('OroCRM\Bundle\CampaignBundle\Transport\VisibilityTransportInterface');
        $transport->expects($this->once())
            ->method('isVisibleInForm')
            ->will($this->returnValue(true));
        $transport->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('t1'));

        $provider->addTransport($transport);
        $this->assertTrue($provider->isVisibleInForm('t1'));
    }

    public function testTransportIsInvisibleInForm()
    {
        $provider = new EmailTransportProvider();
        $transport = $this->getMock('OroCRM\Bundle\CampaignBundle\Transport\VisibilityTransportInterface');
        $transport->expects($this->once())
            ->method('isVisibleInForm')
            ->will($this->returnValue(false));
        $transport->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('t1'));

        $provider->addTransport($transport);
        $this->assertFalse($provider->isVisibleInForm('t1'));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Transport test is unknown
     */
    public function testGetTransportException()
    {
        $provider = new EmailTransportProvider();
        $provider->getTransportByName('test');
    }
}
