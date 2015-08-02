<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use OroCRM\Bundle\MagentoBundle\Form\Type\CartApiType;

class CartApiTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var CartApiType */
    protected $type;

    protected function setUp()
    {
        $this->type = new CartApiType();
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
            'subTotal'          => 'oro_money',
            'grandTotal'        => 'oro_money',
            'taxAmount'         => 'oro_money',
            'cartItems'         => 'orocrm_cart_item_collection',
            'customer'          => 'orocrm_customer_select',
            'store'             => 'translatable_entity',
            'itemsQty'          => 'number',
            'baseCurrencyCode'  => 'text',
            'storeCurrencyCode' => 'text',
            'quoteCurrencyCode' => 'text',
            'storeToBaseRate'   => 'number',
            'storeToQuoteRate'  => 'number',
            'email'             => 'text',
            'giftMessage'       => 'text',
            'isGuest'           => 'checkbox',
            'shippingAddress'   => 'cart_address_api_type',
            'billingAddress'    => 'cart_address_api_type',
            'paymentDetails'    => 'text',
            'status'            => 'translatable_entity',
            'notes'             => 'text',
            'statusMessage'     => 'text',
            'owner'             => 'translatable_entity',
            'dataChannel'       => 'translatable_entity',
            'channel'           => 'oro_integration_select',
            'originId'          => 'number'
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
                    'data_class'      => 'OroCRM\Bundle\MagentoBundle\Entity\Cart',
                    'csrf_protection' => false
                ]
            );

        $this->type->setDefaultOptions($resolver);
    }

    public function testGetName()
    {
        $this->assertEquals('cart_api_type', $this->type->getName());
    }

    public function testGetParent()
    {
        $this->assertEquals('form', $this->type->getParent());
    }
}
