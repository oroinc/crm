<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Unit\Form\Type;

use OroCRM\Bundle\CampaignBundle\Form\Type\EmailCampaignType;

class EmailCampaignTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var EmailCampaignType */
    protected $type;

    protected function setUp()
    {
        $subscriber = $this
            ->getMockBuilder('Oro\Bundle\EmailBundle\Form\EventListener\BuildTemplateFormSubscriber')
            ->disableOriginalConstructor()
            ->getMock();

        $this->type = new EmailCampaignType($subscriber);
    }

    protected function tearDown()
    {
        unset($this->type);
    }

    public function testAddEntityFields()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $builder->expects($this->at(1))
            ->method('add')
            ->with('name', 'text')
            ->will($this->returnSelf());
        $builder->expects($this->at(2))
            ->method('add')
            ->with('schedule', 'choice')
            ->will($this->returnSelf());
        $builder->expects($this->at(3))
            ->method('add')
            ->with('scheduledAt', 'oro_datetime')
            ->will($this->returnSelf());
        $builder->expects($this->at(4))
            ->method('add')
            ->with('campaign', 'orocrm_campaign_select')
            ->will($this->returnSelf());
        $builder->expects($this->at(5))
            ->method('add')
            ->with('marketingList', 'orocrm_marketing_list_select')
            ->will($this->returnSelf());
        $builder->expects($this->at(6))
            ->method('add')
            ->with('template', 'oro_email_template_list')
            ->will($this->returnSelf());
        $builder->expects($this->at(7))
            ->method('add')
            ->with('description', 'textarea')
            ->will($this->returnSelf());

        $this->type->buildForm($builder, []);
    }

    public function testName()
    {
        $typeName = $this->type->getName();
        $this->assertInternalType('string', $typeName);
        $this->assertSame('orocrm_email_campaign', $typeName);
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(['data_class' => 'OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign']);

        $this->type->setDefaultOptions($resolver);
    }
}
