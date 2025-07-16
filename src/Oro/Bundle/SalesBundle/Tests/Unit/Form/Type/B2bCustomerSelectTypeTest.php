<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Form\Type;

use Oro\Bundle\ChannelBundle\Form\Type\CreateOrSelectInlineChannelAwareType;
use Oro\Bundle\SalesBundle\Form\Type\B2bCustomerSelectType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class B2bCustomerSelectTypeTest extends TestCase
{
    private B2bCustomerSelectType $type;

    #[\Override]
    protected function setUp(): void
    {
        $this->type = new B2bCustomerSelectType();
    }

    public function testConfigureOptions(): void
    {
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));
        $this->type->configureOptions($resolver);
    }

    public function testGetParent(): void
    {
        $this->assertEquals(CreateOrSelectInlineChannelAwareType::class, $this->type->getParent());
    }

    public function testGetName(): void
    {
        $this->assertEquals('oro_sales_b2bcustomer_select', $this->type->getName());
    }
}
