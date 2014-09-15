<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Form\Type;

use OroCRM\Bundle\SalesBundle\Form\Type\B2bCustomerSelectType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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

    public function testSetDefaultOptions()
    {
        /** @var OptionsResolverInterface|\PHPUnit_Framework_MockObject_MockObject $resolver */
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));
        $this->type->setDefaultOptions($resolver);
    }

    public function testGetParent()
    {
        $this->assertEquals('oro_entity_create_or_select_inline_channel_aware', $this->type->getParent());
    }

    public function testGetName()
    {
        $this->assertEquals('orocrm_sales_b2bcustomer_select', $this->type->getName());
    }
}
