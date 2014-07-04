<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\FormBuilder;

use OroCRM\Bundle\ChannelBundle\Form\Type\ChannelType;

class ChannelTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var FormBuilder|\PHPUnit_Framework_MockObject_MockObject */
    protected $builder;

    /** @var ChannelType */
    protected $type;

    public function setUp()
    {
        $this->builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()->getMock();

        $this->type = new ChannelType();
    }

    public function tearDown()
    {
        unset($this->type, $this->builder);
    }

    public function testBuildForm()
    {
        $fields = [];
        $this->builder->expects($this->exactly(5))->method('add')
            ->will(
                $this->returnCallback(
                    function ($filedName, $fieldType) use (&$fields) {
                        $fields[$filedName] = $fieldType;
                    }
                )
            );

        $this->type->buildForm($this->builder, []);

        $this->assertSame(
            [
                'name'         => 'text',
                'description'  => 'textarea',
                'entities'     => 'orocrm_channel_entity_choice_form',
                'integrations' => 'genemu_jqueryselect2_entity',
                'dataSource'   => 'genemu_jqueryselect2_entity'
            ],
            $fields
        );
    }

    public function testGetName()
    {
        $this->assertEquals('orocrm_channel_form', $this->type->getName());
    }

    public function testGetParent()
    {
        $this->assertEquals('form', $this->type->getParent());
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));
        $this->type->setDefaultOptions($resolver);
    }
}
