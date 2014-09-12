<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Unit\Model;

use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;
use OroCRM\Bundle\CampaignBundle\Model\EmailCampaignSenderBuilder;

class EmailCampaignSenderBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $campaignSender;

    /**
     * @var EmailCampaignSenderBuilder
     */
    protected $factory;

    protected function setUp()
    {
        $this->campaignSender = $this
            ->getMockBuilder('OroCRM\Bundle\CampaignBundle\Model\EmailCampaignSender')
            ->disableOriginalConstructor()
            ->getMock();

        $this->factory = new EmailCampaignSenderBuilder($this->campaignSender);
    }

    public function testGetSender()
    {
        $emailCampaign = new EmailCampaign();

        $this->campaignSender
            ->expects($this->once())
            ->method('setEmailCampaign')
            ->with($this->equalTo($emailCampaign));

        $this->assertEquals($this->campaignSender, $this->factory->getSender($emailCampaign));
    }
}
