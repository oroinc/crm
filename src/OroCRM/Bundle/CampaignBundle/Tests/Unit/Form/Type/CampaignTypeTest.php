<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Unit\Form\Type;

use OroCRM\Bundle\CampaignBundle\Form\Type\CampaignType;

class CampaignTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var CampaignType */
    protected $type;

    protected function setUp()
    {
        $this->type = new CampaignType();
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

        $builder->expects($this->at(0))
            ->method('add')
            ->with('name', 'text')
            ->will($this->returnSelf());
        $builder->expects($this->at(1))
            ->method('add')
            ->with('code', 'text')
            ->will($this->returnSelf());
        $builder->expects($this->at(2))
            ->method('add')
            ->with('startDate', 'oro_date')
            ->will($this->returnSelf());
        $builder->expects($this->at(3))
            ->method('add')
            ->with('endDate', 'oro_date')
            ->will($this->returnSelf());
        $builder->expects($this->at(4))
            ->method('add')
            ->with('description', 'oro_resizeable_rich_text')
            ->will($this->returnSelf());
        $builder->expects($this->at(5))
            ->method('add')
            ->with('budget', 'oro_money')
            ->will($this->returnSelf());

        $this->type->buildForm($builder, []);
    }

    public function testName()
    {
        $typeName = $this->type->getName();
        $this->assertInternalType('string', $typeName);
        $this->assertSame('orocrm_campaign_form', $typeName);
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with([
                'data_class' => 'OroCRM\Bundle\CampaignBundle\Entity\Campaign',
                'validation_groups' => ['Campaign', 'Default']
            ]);

        $this->type->setDefaultOptions($resolver);
    }
}
