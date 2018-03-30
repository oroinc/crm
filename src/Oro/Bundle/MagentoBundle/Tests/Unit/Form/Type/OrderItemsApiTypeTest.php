<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;
use Oro\Bundle\FormBundle\Form\Type\OroPercentType;
use Oro\Bundle\MagentoBundle\Form\Type\OrderItemsApiType;
use Oro\Bundle\MagentoBundle\Form\Type\OrderSelectType;

class OrderItemsApiTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var OrderItemsApiType */
    protected $type;

    protected function setUp()
    {
        $this->type = new OrderItemsApiType();
    }

    protected function tearDown()
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
            'name'            => 'text',
            'sku'             => 'text',
            'qty'             => 'number',
            'cost'            => OroMoneyType::class,
            'price'           => OroMoneyType::class,
            'weight'          => 'number',
            'taxPercent'      => OroPercentType::class,
            'taxAmount'       => OroMoneyType::class,
            'discountPercent' => OroPercentType::class,
            'discountAmount'  => OroMoneyType::class,
            'rowTotal'        => OroMoneyType::class,
            'order'           => OrderSelectType::class,
            'productType'     => 'text',
            'productOptions'  => 'text',
            'isVirtual'       => 'checkbox',
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

    public function testGetName()
    {
        $this->assertEquals('order_item_api_type', $this->type->getName());
    }
}
