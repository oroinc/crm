<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Form\Type;

use Oro\Bundle\ChannelBundle\Form\Type\CreateOrSelectInlineChannelAwareType;
use Oro\Bundle\SalesBundle\Form\Type\B2bCustomerSelectType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class B2bCustomerSelectTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var B2bCustomerSelectType */
    protected $type;

    /**
     * Setup test env
     */
    protected function setUp(): void
    {
        $this->type = new B2bCustomerSelectType();
    }

    protected function tearDown(): void
    {
        unset($this->type);
    }

    public function testConfigureOptions()
    {
        /** @var OptionsResolver|\PHPUnit\Framework\MockObject\MockObject $resolver */
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));
        $this->type->configureOptions($resolver);
    }

    public function testGetParent()
    {
        $this->assertEquals(CreateOrSelectInlineChannelAwareType::class, $this->type->getParent());
    }

    public function testGetName()
    {
        $this->assertEquals('oro_sales_b2bcustomer_select', $this->type->getName());
    }
}
