<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Form\Type;

use Oro\Bundle\ChannelBundle\Form\Type\ChannelEntityType;

class ChannelEntityTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var ChannelEntityType */
    protected $type;

    public function setUp()
    {
        $this->type = new ChannelEntityType();
    }

    public function tearDown()
    {
        unset($this->type);
    }

    public function testType()
    {
        $this->assertSame('oro_channel_entities', $this->type->getName());
        $this->assertSame('hidden', $this->type->getParent());

        $this->assertInstanceOf('Symfony\Component\Form\AbstractType', $this->type);
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()->getMock();

        $builder->expects($this->once())
            ->method('addViewTransformer')
            ->with($this->isInstanceOf('Oro\Bundle\FormBundle\Form\DataTransformer\ArrayToJsonTransformer'));

        $this->type->buildForm($builder, []);
    }
}
