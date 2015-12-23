<?php

namespace OroCRM\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\SoapBundle\Form\EventListener\PatchSubscriber;

use OroCRM\Bundle\MagentoBundle\Form\EventListener\OrderApiFormSubscriber;

class OrderApiType extends AbstractType
{
    const NAME = 'order_api_type';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('incrementId', 'text', ['required' => true]);
        $builder->add('isVirtual', 'checkbox', ['required' => false]);
        $builder->add('isGuest', 'checkbox', ['required' => false]);
        $builder->add('giftMessage', 'text', ['required' => false]);
        $builder->add('remoteIp', 'text', ['required' => false]);
        $builder->add('storeName', 'text', ['required' => false]);
        $builder->add('totalPaidAmount', 'number', ['required' => false]);
        $builder->add('totalInvoicedAmount', 'oro_money', ['required' => false]);
        $builder->add('totalRefundedAmount', 'oro_money', ['required' => false]);
        $builder->add('totalCanceledAmount', 'oro_money', ['required' => false]);
        $builder->add('notes', 'text', ['required' => false]);
        $builder->add('feedback', 'text', ['required' => false]);
        $builder->add('customerEmail', 'text', ['required' => false]);
        $builder->add('currency', 'text', ['required' => false]);
        $builder->add('paymentMethod', 'text', ['required' => false]);
        $builder->add('paymentDetails', 'text', ['required' => false]);
        $builder->add('subtotalAmount', 'oro_money', ['required' => false]);
        $builder->add('shippingAmount', 'oro_money', ['required' => false]);
        $builder->add('shippingMethod', 'text', ['required' => false]);
        $builder->add('taxAmount', 'oro_money', ['required' => false]);
        $builder->add('couponCode', 'text', ['required' => false]);
        $builder->add('discountAmount', 'oro_money', ['required' => false]);
        $builder->add('discountPercent', 'oro_percent', ['required' => false]);
        $builder->add('totalAmount', 'oro_money', ['required' => false]);
        $builder->add('status', 'text', ['required' => true]);

        $builder->add('customer', 'orocrm_customer_select', ['required' => false]);

        $builder->add(
            'addresses',
            'oro_address_collection',
            [
                'label'    => '',
                'type'     => 'oro_typed_address',
                'required' => true,
                'options'  => ['data_class' => 'OroCRM\Bundle\MagentoBundle\Entity\OrderAddress']
            ]
        );

        $builder->add(
            'items',
            'orocrm_order_item_collection',
            [
                'label'    => '',
                'type'     => 'orocrm_order_item',
                'required' => true,
                'options'  => ['data_class' => 'OroCRM\Bundle\MagentoBundle\Entity\OrderItem']
            ]
        );

        $builder->add(
            'owner',
            'translatable_entity',
            [
                'class'    => 'Oro\Bundle\UserBundle\Entity\User',
                'property' => 'username',
                'required' => false
            ]
        );

        $builder->add(
            'dataChannel',
            'translatable_entity',
            [
                'class'    => 'OroCRMChannelBundle:Channel',
                'property' => 'name',
                'required' => false
            ]
        );

        $builder->add(
            'store',
            'translatable_entity',
            [
                'class'    => 'OroCRMMagentoBundle:Store',
                'property' => 'name'
            ]
        );

        $builder->add('channel', 'oro_integration_select');

        $builder->addEventSubscriber(new PatchSubscriber());
        $builder->addEventSubscriber(new OrderApiFormSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'      => 'OroCRM\Bundle\MagentoBundle\Entity\Order',
                'csrf_protection' => false
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
