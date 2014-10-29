<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Unit\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use OroCRM\Bundle\CampaignBundle\Form\Type\EmailTransportSelectType;

class EmailTransportSelectTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EmailTransportSelectType
     */
    protected $type;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $emailTransportProvider;

    /**
     * Setup test env
     */
    protected function setUp()
    {
        $this->emailTransportProvider = $this
            ->getMockBuilder('OroCRM\Bundle\CampaignBundle\Provider\EmailTransportProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $this->type = new EmailTransportSelectType($this->emailTransportProvider);
    }

    public function testSetDefaultOptions()
    {
        $choices = ['internal' => 'orocrm.campaign.emailcampaign.transport.internal'];
        $this->emailTransportProvider
            ->expects($this->once())
            ->method('getVisibleTransportChoices')
            ->will($this->returnValue($choices));
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver
            ->expects($this->once())
            ->method('setDefaults')
            ->with(['choices' => $choices]);
        $this->type->setDefaultOptions($resolver);
    }

    public function testGetParent()
    {
        $this->assertEquals('choice', $this->type->getParent());
    }

    public function testGetName()
    {
        $this->assertEquals('orocrm_campaign_email_transport_select', $this->type->getName());
    }
}
