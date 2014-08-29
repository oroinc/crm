<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Form\Type;

use OroCRM\Bundle\ChannelBundle\Form\Type\ChannelSelectType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ChannelSelectTypetest extends \PHPUnit_Framework_TestCase
{
    /** @var ChannelSelectType */
    protected $type;

    public function setUp()
    {
        $this->type = new ChannelSelectType();
    }

    public function tearDown()
    {
        unset($this->type);
    }

    public function testGetName()
    {
        $this->assertEquals(
            'orocrm_channel_select_type',
            $this->type->getName()
        );
    }

    public function testGetParent()
    {
        $this->assertEquals(
            'genemu_jqueryselect2_entity',
            $this->type->getParent()
        );
    }

    public function testSetDefaultOptions()
    {
        /** @var OptionsResolverInterface|\PHPUnit_Framework_MockObject_MockObject $resolver */
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));
        $this->type->setDefaultOptions($resolver);
    }
}
