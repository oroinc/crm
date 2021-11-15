<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Form\Type;

use Oro\Bundle\ChannelBundle\Form\Type\ChannelEntityType;
use Oro\Bundle\FormBundle\Form\DataTransformer\ArrayToJsonTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilder;

class ChannelEntityTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var ChannelEntityType */
    private $type;

    protected function setUp(): void
    {
        $this->type = new ChannelEntityType();
    }

    public function testType()
    {
        $this->assertSame('oro_channel_entities', $this->type->getName());
        $this->assertSame(HiddenType::class, $this->type->getParent());

        $this->assertInstanceOf(AbstractType::class, $this->type);
    }

    public function testBuildForm()
    {
        $builder = $this->createMock(FormBuilder::class);

        $builder->expects($this->once())
            ->method('addViewTransformer')
            ->with($this->isInstanceOf(ArrayToJsonTransformer::class));

        $this->type->buildForm($builder, []);
    }
}
