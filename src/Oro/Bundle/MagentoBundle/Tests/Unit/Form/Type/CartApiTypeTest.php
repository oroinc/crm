<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;
use Oro\Bundle\IntegrationBundle\Form\Type\IntegrationSelectType;
use Oro\Bundle\MagentoBundle\Form\Type\CartAddressApiType;
use Oro\Bundle\MagentoBundle\Form\Type\CartApiType;
use Oro\Bundle\MagentoBundle\Form\Type\CartItemCollectionType;
use Oro\Bundle\MagentoBundle\Form\Type\CustomerSelectType;
use Oro\Bundle\TranslationBundle\Form\Type\TranslatableEntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CartApiTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var CartApiType */
    protected $type;

    protected function setUp(): void
    {
        $this->type = new CartApiType();
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
            'subTotal'          => OroMoneyType::class,
            'grandTotal'        => OroMoneyType::class,
            'taxAmount'         => OroMoneyType::class,
            'cartItems'         => CartItemCollectionType::class,
            'customer'          => CustomerSelectType::class,
            'store'             => TranslatableEntityType::class,
            'itemsQty'          => NumberType::class,
            'baseCurrencyCode'  => TextType::class,
            'storeCurrencyCode' => TextType::class,
            'quoteCurrencyCode' => TextType::class,
            'storeToBaseRate'   => NumberType::class,
            'storeToQuoteRate'  => NumberType::class,
            'email'             => TextType::class,
            'giftMessage'       => TextType::class,
            'isGuest'           => CheckboxType::class,
            'shippingAddress'   => CartAddressApiType::class,
            'billingAddress'    => CartAddressApiType::class,
            'paymentDetails'    => TextType::class,
            'status'            => TranslatableEntityType::class,
            'notes'             => TextType::class,
            'statusMessage'     => TextType::class,
            'owner'             => TranslatableEntityType::class,
            'dataChannel'       => TranslatableEntityType::class,
            'channel'           => IntegrationSelectType::class,
            'originId'          => NumberType::class
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
                    'data_class'      => 'Oro\Bundle\MagentoBundle\Entity\Cart',
                    'csrf_protection' => false
                ]
            );

        $this->type->configureOptions($resolver);
    }
}
