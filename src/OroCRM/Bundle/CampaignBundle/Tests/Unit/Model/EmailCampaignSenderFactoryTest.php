<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Unit\Model;

use OroCRM\Bundle\CampaignBundle\Model\EmailCampaignSenderFactory;

class EmailCampaignSenderFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $container;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $emailTransportProvider;

    /**
     * @var EmailCampaignSenderFactory
     */
    protected $factory;

    protected function setUp()
    {
        $this->container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->getMock();
        $this->emailTransportProvider = $this
            ->getMockBuilder('OroCRM\Bundle\CampaignBundle\Provider\EmailTransportProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $this->factory = new EmailCampaignSenderFactory($this->container, $this->emailTransportProvider);
    }

    public function testGetSender()
    {
        $emailCampaign = $this
            ->getMockBuilder('OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign')
            ->disableOriginalConstructor()
            ->getMock();
        $emailCampaign
            ->expects($this->once())
            ->method('getTransport')
            ->will($this->returnValue('test'));
        $transport = $this->getMock('OroCRM\Bundle\CampaignBundle\Transport\TransportInterface');
        $sender = $this
            ->getMockBuilder('OroCRM\Bundle\CampaignBundle\Model\EmailCampaignSender')
            ->disableOriginalConstructor()
            ->getMock();
        $this->emailTransportProvider
            ->expects($this->once())
            ->method('getTransportByName')
            ->with('test')
            ->will($this->returnValue($transport));
        $this->container
            ->expects($this->once())
            ->method('get')
            ->with('orocrm_campaign.email_campaign.sender')
            ->will($this->returnValue($sender));
        $sender
            ->expects($this->once())
            ->method('setTransport')
            ->with($transport);

        $this->assertEquals($sender, $this->factory->getSender($emailCampaign));
    }
}
