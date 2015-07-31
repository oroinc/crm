<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use OroCRM\Bundle\MagentoBundle\Form\Type\CartItemsApiType;

class CartItemsApiTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var CartItemsApiType */
    protected $type;

    protected function setUp()
    {
        $this->type = new CartItemsApiType();
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

        $builder->expects($this->exactly(2))
            ->method('addEventSubscriber')
            ->with($this->isInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface'));

        $expectedFields = [
            'sku'            => 'text',
            'name'           => 'text',
            'qty'            => 'number',
            'price'          => 'oro_money',
            'discountAmount' => 'oro_money',
            'taxPercent'     => 'oro_percent',
            'weight'         => 'number',
            'productId'      => 'number',
            'parentItemId'   => 'number',
            'freeShipping'   => 'text',
            'taxAmount'      => 'oro_money',
            'giftMessage'    => 'text',
            'taxClassId'     => 'text',
            'description'    => 'text',
            'isVirtual'      => 'checkbox',
            'customPrice'    => 'oro_money',
            'priceInclTax'   => 'oro_money',
            'rowTotal'       => 'oro_money',
            'productType'    => 'text',
            'cart'           => 'orocrm_cart_select'
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
                    'data_class'           => 'OroCRM\Bundle\MagentoBundle\Entity\CartItem',
                    'intention'            => 'items',
                    'extra_fields_message' => 'This form should not contain extra fields: "{{ extra_fields }}"',
                    'single_form'          => true,
                    'csrf_protection'      => false
                ]
            );

        $this->type->setDefaultOptions($resolver);
    }

    public function testGetName()
    {
        $this->assertEquals('cart_item_api_type', $this->type->getName());
    }

    public function testGetParent()
    {
        $this->assertEquals('form', $this->type->getParent());
    }
}
