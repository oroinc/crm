<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Form\Type;

use Oro\Bundle\SalesBundle\Form\Type\B2bCustomerSelectType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class B2bCustomerSelectTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var B2bCustomerSelectType */
    protected $type;

    /**
     * Setup test env
     */
    protected function setUp()
    {
        $this->type = new B2bCustomerSelectType();
    }

    protected function tearDown()
    {
        unset($this->type);
    }

    public function testConfigureOptions()
    {
        /** @var OptionsResolver|\PHPUnit_Framework_MockObject_MockObject $resolver */
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));
        $this->type->configureOptions($resolver);
    }

    public function testGetParent()
    {
        $this->assertEquals('oro_entity_create_or_select_inline_channel_aware', $this->type->getParent());
    }

    public function testGetName()
    {
        $this->assertEquals('oro_sales_b2bcustomer_select', $this->type->getName());
    }
}
