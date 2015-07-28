<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use OroCRM\Bundle\MagentoBundle\Form\Type\CartSelectType;

class CartSelectTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var CartSelectType */
    protected $type;

    protected function setUp()
    {
        $this->type = new CartSelectType();
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
                        'placeholder'             => 'orocrm.magento.form.choose_cart',
                        'result_template_twig'    => 'OroCRMMagentoBundle:Cart:Autocomplete/result.html.twig',
                        'selection_template_twig' => 'OroCRMMagentoBundle:Cart:Autocomplete/selection.html.twig'
                    ],
                    'autocomplete_alias' => 'orocrm_magento.carts',
                ]
            );

        $this->type->setDefaultOptions($resolver);
    }

    public function testGetName()
    {
        $this->assertEquals('orocrm_cart_select', $this->type->getName());
    }

    public function testGetParent()
    {
        $this->assertEquals('oro_jqueryselect2_hidden', $this->type->getParent());
    }
}
