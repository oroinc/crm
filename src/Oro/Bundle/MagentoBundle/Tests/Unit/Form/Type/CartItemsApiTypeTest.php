<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;
use Oro\Bundle\FormBundle\Form\Type\OroPercentType;
use Oro\Bundle\MagentoBundle\Form\Type\CartItemsApiType;
use Oro\Bundle\MagentoBundle\Form\Type\CartSelectType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CartItemsApiTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var CartItemsApiType */
    protected $type;

    protected function setUp(): void
    {
        $this->type = new CartItemsApiType();
    }

    protected function tearDown(): void
    {
        unset($this->type);
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $builder->expects($this->exactly(2))
            ->method('addEventSubscriber')
            ->with($this->isInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface'));

        $expectedFields = [
            'sku'            => TextType::class,
            'name'           => TextType::class,
            'qty'            => NumberType::class,
            'price'          => OroMoneyType::class,
            'discountAmount' => OroMoneyType::class,
            'taxPercent'     => OroPercentType::class,
            'weight'         => NumberType::class,
            'productId'      => NumberType::class,
            'parentItemId'   => NumberType::class,
            'freeShipping'   => TextType::class,
            'taxAmount'      => OroMoneyType::class,
            'giftMessage'    => TextType::class,
            'taxClassId'     => TextType::class,
            'description'    => TextType::class,
            'isVirtual'      => CheckboxType::class,
            'customPrice'    => OroMoneyType::class,
            'priceInclTax'   => OroMoneyType::class,
            'rowTotal'       => OroMoneyType::class,
            'productType'    => TextType::class,
            'cart'           => CartSelectType::class
        ];

        $builder->expects($this->exactly(count($expectedFields)))
            ->method('add');

        $counter = 0;
        foreach ($expectedFields as $fieldName => $formType) {
            $builder->expects($this->at($counter))
                ->method('add')
                ->with($fieldName, $formType)
                ->will($this->returnSelf());
            $counter++;
        }

        $this->type->buildForm($builder, []);
    }

    public function testConfigureOptions()
    {
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                [
                    'data_class'           => 'Oro\Bundle\MagentoBundle\Entity\CartItem',
                    'csrf_token_id'        => 'items',
                    'single_form'          => true,
                    'csrf_protection'      => false
                ]
            );

        $this->type->configureOptions($resolver);
    }
}
