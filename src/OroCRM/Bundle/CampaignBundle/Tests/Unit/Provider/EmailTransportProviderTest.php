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

    public function testTransportActualChoices()
    {
        $choices = ['t1' => 'Transport 1', 't2' => 'Transport 2'];
        $provider = new EmailTransportProvider();
        $transportOne = $this->getMock('OroCRM\Bundle\CampaignBundle\Transport\TransportInterface');
        $transportOne->expects($this->exactly(2))
            ->method('getName')
            ->will($this->returnValue('t1'));
        $transportOne->expects($this->once())
            ->method('getLabel')
            ->will($this->returnValue('Transport 1'));
        $transportTwo = $this->getMock('OroCRM\Bundle\CampaignBundle\Transport\TransportInterface');
        $transportTwo->expects($this->exactly(2))
            ->method('getName')
            ->will($this->returnValue('t2'));
        $transportTwo->expects($this->once())
            ->method('getLabel')
            ->will($this->returnValue('Transport 2'));
        $transportTree = $this->getMock('OroCRM\Bundle\CampaignBundle\Tests\Unit\Provider\TransportStub');
        $transportTree->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('t3'));
        $transportTree->expects($this->never())
            ->method('getLabel')
            ->will($this->returnValue('Transport 3'));
        $transportTree->expects($this->once())
            ->method('isVisibleInForm')
            ->will($this->returnValue(false));

        $provider->addTransport($transportOne);
        $provider->addTransport($transportTwo);
        $provider->addTransport($transportTree);
        $this->assertEquals($choices, $provider->getVisibleTransportChoices());
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
