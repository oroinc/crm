<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use Oro\Bundle\MagentoBundle\Form\Type\CustomerSelectType;

class CustomerSelectTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var CustomerSelectType */
    protected $type;

    protected function setUp()
    {
        $this->type = new CustomerSelectType();
    }

    protected function tearDown()
    {
        unset($this->type);
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                [
                    'configs'            => [
                        'placeholder' => 'oro.magento.form.choose_customer'
                    ],
                    'autocomplete_alias' => 'oro_magento.customers'
                ]
            );

        $this->type->setDefaultOptions($resolver);
    }

    public function testGetName()
    {
        $this->assertEquals('oro_customer_select', $this->type->getName());
    }

    public function testGetParent()
    {
        $this->assertEquals('oro_jqueryselect2_hidden', $this->type->getParent());
    }
}
