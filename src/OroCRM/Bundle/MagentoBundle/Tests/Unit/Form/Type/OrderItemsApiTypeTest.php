<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use OroCRM\Bundle\MagentoBundle\Form\Type\OrderItemsApiType;

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
            'cost'            => 'oro_money',
            'price'           => 'oro_money',
            'weight'          => 'number',
            'taxPercent'      => 'oro_percent',
            'taxAmount'       => 'oro_money',
            'discountPercent' => 'oro_percent',
            'discountAmount'  => 'oro_money',
            'rowTotal'        => 'oro_money',
            'order'           => 'orocrm_order_select',
            'productType'     => 'text',
            'productOptions'  => 'text',
            'isVirtual'       => 'checkbox',
            'originalPrice'   => 'oro_money',
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

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                [
                    'data_class'      => 'OroCRM\Bundle\MagentoBundle\Entity\OrderItem',
                    'csrf_protection' => false,
                ]
            );

        $this->type->setDefaultOptions($resolver);
    }

    public function testGetName()
    {
        $this->assertEquals('order_item_api_type', $this->type->getName());
    }

    public function testGetParent()
    {
        $this->assertEquals('form', $this->type->getParent());
    }
}
