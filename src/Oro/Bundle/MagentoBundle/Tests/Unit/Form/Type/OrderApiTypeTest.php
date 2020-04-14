<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\AddressCollectionType;
use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;
use Oro\Bundle\FormBundle\Form\Type\OroPercentType;
use Oro\Bundle\IntegrationBundle\Form\Type\IntegrationSelectType;
use Oro\Bundle\MagentoBundle\Form\Type\CustomerSelectType;
use Oro\Bundle\MagentoBundle\Form\Type\OrderApiType;
use Oro\Bundle\MagentoBundle\Form\Type\OrderItemCollectionType;
use Oro\Bundle\TranslationBundle\Form\Type\TranslatableEntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class OrderApiTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var OrderApiType */
    protected $type;

    protected function setUp(): void
    {
        $this->type = new OrderApiType();
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
            'incrementId'         => TextType::class,
            'originId'            => TextType::class,
            'isVirtual'           => CheckboxType::class,
            'isGuest'             => CheckboxType::class,
            'giftMessage'         => TextType::class,
            'remoteIp'            => TextType::class,
            'storeName'           => TextType::class,
            'totalPaidAmount'     => NumberType::class,
            'totalInvoicedAmount' => OroMoneyType::class,
            'totalRefundedAmount' => OroMoneyType::class,
            'totalCanceledAmount' => OroMoneyType::class,
            'notes'               => TextType::class,
            'feedback'            => TextType::class,
            'customerEmail'       => TextType::class,
            'currency'            => TextType::class,
            'paymentMethod'       => TextType::class,
            'paymentDetails'      => TextType::class,
            'subtotalAmount'      => OroMoneyType::class,
            'shippingAmount'      => OroMoneyType::class,
            'shippingMethod'      => TextType::class,
            'taxAmount'           => OroMoneyType::class,
            'couponCode'          => TextType::class,
            'discountAmount'      => OroMoneyType::class,
            'discountPercent'     => OroPercentType::class,
            'totalAmount'         => OroMoneyType::class,
            'status'              => TextType::class,
            'customer'            => CustomerSelectType::class,
            'addresses'           => AddressCollectionType::class,
            'items'               => OrderItemCollectionType::class,
            'owner'               => TranslatableEntityType::class,
            'dataChannel'         => TranslatableEntityType::class,
            'store'               => TranslatableEntityType::class,
            'channel'             => IntegrationSelectType::class
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
                    'data_class'      => 'Oro\Bundle\MagentoBundle\Entity\Order',
                    'csrf_protection' => false
                ]
            );

        $this->type->configureOptions($resolver);
    }
}
