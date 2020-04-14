<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroJquerySelect2HiddenType;
use Oro\Bundle\MagentoBundle\Form\Type\CustomerSelectType;

class CustomerSelectTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var CustomerSelectType */
    protected $type;

    protected function setUp(): void
    {
        $this->type = new CustomerSelectType();
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
                        'placeholder' => 'oro.magento.form.choose_customer'
                    ],
                    'autocomplete_alias' => 'oro_magento.customers'
                ]
            );

        $this->type->configureOptions($resolver);
    }

    public function testGetParent()
    {
        $this->assertEquals(OroJquerySelect2HiddenType::class, $this->type->getParent());
    }
}
