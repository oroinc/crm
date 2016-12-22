<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CampaignBundle\Form\Type\EmailCampaignType;
use Symfony\Component\Form\FormEvents;

class EmailCampaignTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var EmailCampaignType */
    protected $type;

    protected function setUp()
    {
        $transportProvider = $this
            ->getMockBuilder('Oro\Bundle\CampaignBundle\Provider\EmailTransportProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->type = new EmailCampaignType($transportProvider);
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

        $builder->expects($this->atLeastOnce())
            ->method('add')
            ->with($this->isType('string'), $this->isType('string'))
            ->will($this->returnSelf());

        $builder->expects($this->once())
            ->method('addEventListener')
            ->with(FormEvents::PRE_SET_DATA);

        $subscriber = $this->createMock('Symfony\Component\EventDispatcher\EventSubscriberInterface');
        $builder->expects($this->once())
            ->method('addEventSubscriber')
            ->with($subscriber);

        $this->type->addSubscriber($subscriber);
        $this->type->buildForm($builder, []);
    }

    public function testName()
    {
        $typeName = $this->type->getName();
        $this->assertInternalType('string', $typeName);
        $this->assertSame('oro_email_campaign', $typeName);
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver
            ->expects($this->once())
            ->method('setDefaults')
            ->with(
                [
                    'data_class'         => 'Oro\Bundle\CampaignBundle\Entity\EmailCampaign',
                    'cascade_validation' => true
                ]
            );

        $this->type->setDefaultOptions($resolver);
    }
}
