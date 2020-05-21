<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;
use Oro\Bundle\FormBundle\Form\Type\OroPercentType;
use Oro\Bundle\MagentoBundle\Form\Type\OrderItemsApiType;
use Oro\Bundle\MagentoBundle\Form\Type\OrderSelectType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class OrderItemsApiTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var OrderItemsApiType */
    protected $type;

    protected function setUp(): void
    {
        $this->type = new OrderItemsApiType();
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

        $builder->expects($this->once())
            ->method('addEventSubscriber')
            ->with($this->isInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface'));

        $expectedFields = [
            'name'            => TextType::class,
            'sku'             => TextType::class,
            'qty'             => NumberType::class,
            'cost'            => OroMoneyType::class,
            'price'           => OroMoneyType::class,
            'weight'          => NumberType::class,
            'taxPercent'      => OroPercentType::class,
            'taxAmount'       => OroMoneyType::class,
            'discountPercent' => OroPercentType::class,
            'discountAmount'  => OroMoneyType::class,
            'rowTotal'        => OroMoneyType::class,
            'order'           => OrderSelectType::class,
            'productType'     => TextType::class,
            'productOptions'  => TextType::class,
            'isVirtual'       => CheckboxType::class,
            'originalPrice'   => OroMoneyType::class,
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
                    'data_class'      => 'Oro\Bundle\MagentoBundle\Entity\OrderItem',
                    'csrf_protection' => false,
                ]
            );

        $this->type->configureOptions($resolver);
    }
}
