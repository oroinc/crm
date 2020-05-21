<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroJquerySelect2HiddenType;
use Oro\Bundle\MagentoBundle\Form\Type\CartSelectType;

class CartSelectTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var CartSelectType */
    protected $type;

    protected function setUp(): void
    {
        $this->type = new CartSelectType();
    }

    protected function tearDown(): void
    {
        unset($this->type);
    }

    public function testConfigureOptions()
    {
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                [
                    'configs'            => [
                        'placeholder'             => 'oro.magento.form.choose_cart',
                        'result_template_twig'    => 'OroMagentoBundle:Cart:Autocomplete/result.html.twig',
                        'selection_template_twig' => 'OroMagentoBundle:Cart:Autocomplete/selection.html.twig'
                    ],
                    'autocomplete_alias' => 'oro_magento.carts',
                ]
            );

        $this->type->configureOptions($resolver);
    }

    public function testGetParent()
    {
        $this->assertEquals(OroJquerySelect2HiddenType::class, $this->type->getParent());
    }
}
