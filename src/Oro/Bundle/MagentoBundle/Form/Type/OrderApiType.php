<?php

namespace Oro\Bundle\MagentoBundle\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\AddressCollectionType;
use Oro\Bundle\AddressBundle\Form\Type\TypedAddressType;
use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;
use Oro\Bundle\FormBundle\Form\Type\OroPercentType;
use Oro\Bundle\IntegrationBundle\Form\Type\IntegrationSelectType;
use Oro\Bundle\MagentoBundle\Form\EventListener\OrderApiFormSubscriber;
use Oro\Bundle\SoapBundle\Form\EventListener\PatchSubscriber;
use Oro\Bundle\TranslationBundle\Form\Type\TranslatableEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderApiType extends AbstractType
{
    const NAME = 'order_api_type';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('incrementId', TextType::class, ['required' => true]);
        $builder->add('originId', TextType::class, ['required' => false]);
        $builder->add('isVirtual', CheckboxType::class, ['required' => false]);
        $builder->add('isGuest', CheckboxType::class, ['required' => false]);
        $builder->add('giftMessage', TextType::class, ['required' => false]);
        $builder->add('remoteIp', TextType::class, ['required' => false]);
        $builder->add('storeName', TextType::class, ['required' => false]);
        $builder->add('totalPaidAmount', NumberType::class, ['required' => false]);
        $builder->add('totalInvoicedAmount', OroMoneyType::class, ['required' => false]);
        $builder->add('totalRefundedAmount', OroMoneyType::class, ['required' => false]);
        $builder->add('totalCanceledAmount', OroMoneyType::class, ['required' => false]);
        $builder->add('notes', TextType::class, ['required' => false]);
        $builder->add('feedback', TextType::class, ['required' => false]);
        $builder->add('customerEmail', TextType::class, ['required' => false]);
        $builder->add('currency', TextType::class, ['required' => false]);
        $builder->add('paymentMethod', TextType::class, ['required' => false]);
        $builder->add('paymentDetails', TextType::class, ['required' => false]);
        $builder->add('subtotalAmount', OroMoneyType::class, ['required' => false]);
        $builder->add('shippingAmount', OroMoneyType::class, ['required' => false]);
        $builder->add('shippingMethod', TextType::class, ['required' => false]);
        $builder->add('taxAmount', OroMoneyType::class, ['required' => false]);
        $builder->add('couponCode', TextType::class, ['required' => false]);
        $builder->add('discountAmount', OroMoneyType::class, ['required' => false]);
        $builder->add('discountPercent', OroPercentType::class, ['required' => false]);
        $builder->add('totalAmount', OroMoneyType::class, ['required' => false]);
        $builder->add('status', TextType::class, ['required' => true]);

        $builder->add('customer', CustomerSelectType::class, ['required' => false]);

        $builder->add(
            'addresses',
            AddressCollectionType::class,
            [
                'label'    => '',
                'entry_type'     => TypedAddressType::class,
                'required' => true,
                'entry_options'  => ['data_class' => 'Oro\Bundle\MagentoBundle\Entity\OrderAddress']
            ]
        );

        $builder->add(
            'items',
            OrderItemCollectionType::class,
            [
                'label'    => '',
                'entry_type'     => OrderItemType::class,
                'required' => true,
                'entry_options'  => ['data_class' => 'Oro\Bundle\MagentoBundle\Entity\OrderItem']
            ]
        );

        $builder->add(
            'owner',
            TranslatableEntityType::class,
            [
                'class' => 'Oro\Bundle\UserBundle\Entity\User',
                'choice_label' => 'username',
                'required' => false
            ]
        );

        $builder->add(
            'dataChannel',
            TranslatableEntityType::class,
            [
                'class' => 'OroChannelBundle:Channel',
                'choice_label' => 'name',
                'required' => false
            ]
        );

        $builder->add(
            'store',
            TranslatableEntityType::class,
            [
                'class'    => 'OroMagentoBundle:Store',
                'choice_label' => 'name'
            ]
        );

        $builder->add('channel', IntegrationSelectType::class);

        $builder->addEventSubscriber(new PatchSubscriber());
        $builder->addEventSubscriber(new OrderApiFormSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'      => 'Oro\Bundle\MagentoBundle\Entity\Order',
                'csrf_protection' => false
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}
