<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Unit\Form\Type;

use OroCRM\Bundle\MarketingListBundle\Form\Type\MarketingListType;

class MarketingListTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MarketingListType
     */
    protected $type;

    protected function setUp()
    {
        $this->type = new MarketingListType();
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $builder->expects($this->at(0))
            ->method('add')
            ->with(
                'name',
                'text',
                ['required' => true]
            )
            ->will($this->returnSelf());

        $builder->expects($this->at(1))
            ->method('add')
            ->with(
                'entity',
                'orocrm_marketing_list_contact_information_entity_choice',
                ['required' => true]
            )
            ->will($this->returnSelf());

        $builder->expects($this->at(2))
            ->method('add')
            ->with(
                'description',
                'oro_resizeable_rich_text',
                ['required' => false]
            )
            ->will($this->returnSelf());

        $builder->expects($this->at(4))
            ->method('add')
            ->with(
                'definition',
                'hidden',
                ['required' => false]
            )
            ->will($this->returnSelf());

        $this->type->buildForm($builder, []);
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                [
                    'column_column_choice_type'   => 'hidden',
                    'filter_column_choice_type'   => 'oro_entity_field_select',
                    'data_class'                  => 'OroCRM\Bundle\MarketingListBundle\Entity\MarketingList',
                    'intention'                   => 'marketing_list',
                    'cascade_validation'          => true
                ]
            );

        $this->type->setDefaultOptions($resolver);
    }

    public function testGetName()
    {
        $this->assertEquals('orocrm_marketing_list', $this->type->getName());
    }
}
