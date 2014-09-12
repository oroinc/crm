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
        $transport = $this->getMock('OroCRM\Bundle\CampaignBundle\Transport\TransportInterface');
        $transport
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('internal'));
        $transport
            ->expects($this->once())
            ->method('getLabel')
            ->will($this->returnValue('orocrm.campaign.emailcampaign.transport.internal'));
        $transports = ['internal' => $transport];
        $this->emailTransportProvider
            ->expects($this->once())
            ->method('getTransports')
            ->will($this->returnValue($transports));
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
