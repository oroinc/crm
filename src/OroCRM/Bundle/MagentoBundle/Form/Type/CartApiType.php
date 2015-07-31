<?php

namespace OroCRM\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\SoapBundle\Form\EventListener\PatchSubscriber;

use OroCRM\Bundle\MagentoBundle\Form\EventListener\CartApiFormSubscriber;

class CartApiType extends AbstractType
{
    const NAME = 'cart_api_type';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('subTotal', 'oro_money', ['required' => false]);
        $builder->add('grandTotal', 'oro_money', ['required' => false]);
        $builder->add('taxAmount', 'oro_money', ['required' => false]);
        $builder->add(
            'cartItems',
            'orocrm_cart_item_collection',
            [
                'label'    => '',
                'type'     => 'cart_item_api_type',
                'required' => true,
                'options'  => ['data_class' => 'OroCRM\Bundle\MagentoBundle\Entity\CartItem']
            ]
        );
        $builder->add('customer', 'orocrm_customer_select', ['required' => false]);
        $builder->add(
            'store',
            'translatable_entity',
            [
                'class'    => 'OroCRMMagentoBundle:Store',
                'property' => 'name'
            ]
        );
        $builder->add('itemsQty', 'number', ['required' => true]);
        $builder->add('baseCurrencyCode', 'text', ['required' => true]);
        $builder->add('storeCurrencyCode', 'text', ['required' => true]);
        $builder->add('quoteCurrencyCode', 'text', ['required' => true]);
        $builder->add('storeToBaseRate', 'number', ['required' => true]);
        $builder->add('storeToQuoteRate', 'number', ['required' => false]);
        $builder->add('email', 'text', ['required' => false]);
        $builder->add('giftMessage', 'text', ['required' => false]);
        $builder->add('isGuest', 'checkbox', ['required' => true]);
        $builder->add('shippingAddress', 'cart_address_api_type');
        $builder->add('billingAddress', 'cart_address_api_type');
        $builder->add('paymentDetails', 'text', ['required' => false]);
        $builder->add(
            'status',
            'translatable_entity',
            [
                'class'    => 'OroCRMMagentoBundle:CartStatus',
                'property' => 'name'
            ]
        );
        $builder->add('notes', 'text', ['required' => false]);
        $builder->add('statusMessage', 'text', ['required' => false]);
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
        $builder->add('channel', 'oro_integration_select');
        $builder->add('originId', 'number', ['required' => false]);

        $builder->addEventSubscriber(new PatchSubscriber());
        $builder->addEventSubscriber(new CartApiFormSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'      => 'OroCRM\Bundle\MagentoBundle\Entity\Cart',
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
