<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Form\Type;

use Oro\Bundle\ChannelBundle\Form\Type\ChannelEntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class ChannelEntityTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var ChannelEntityType */
    protected $type;

    protected function setUp(): void
    {
        $this->type = new ChannelEntityType();
    }

    protected function tearDown(): void
    {
        unset($this->type);
    }

    public function testType()
    {
        $this->assertSame('oro_channel_entities', $this->type->getName());
        $this->assertSame(HiddenType::class, $this->type->getParent());

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
