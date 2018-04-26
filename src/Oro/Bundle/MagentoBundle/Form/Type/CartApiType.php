<?php

namespace Oro\Bundle\MagentoBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;
use Oro\Bundle\IntegrationBundle\Form\Type\IntegrationSelectType;
use Oro\Bundle\MagentoBundle\Form\EventListener\CartApiFormSubscriber;
use Oro\Bundle\SoapBundle\Form\EventListener\PatchSubscriber;
use Oro\Bundle\TranslationBundle\Form\Type\TranslatableEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CartApiType extends AbstractType
{
    const NAME = 'cart_api_type';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('subTotal', OroMoneyType::class, ['required' => false]);
        $builder->add('grandTotal', OroMoneyType::class, ['required' => false]);
        $builder->add('taxAmount', OroMoneyType::class, ['required' => false]);
        $builder->add(
            'cartItems',
            CartItemCollectionType::class,
            [
                'label'    => '',
                'entry_type' => CartItemsApiType::class,
                'required' => true,
                'entry_options'  => ['data_class' => 'Oro\Bundle\MagentoBundle\Entity\CartItem']
            ]
        );
        $builder->add('customer', CustomerSelectType::class, ['required' => false]);
        $builder->add(
            'store',
            TranslatableEntityType::class,
            [
                'class' => 'OroMagentoBundle:Store',
                'choice_label' => 'name'
            ]
        );
        $builder->add('itemsQty', NumberType::class, ['required' => true]);
        $builder->add('baseCurrencyCode', TextType::class, ['required' => true]);
        $builder->add('storeCurrencyCode', TextType::class, ['required' => true]);
        $builder->add('quoteCurrencyCode', TextType::class, ['required' => true]);
        $builder->add('storeToBaseRate', NumberType::class, ['required' => true]);
        $builder->add('storeToQuoteRate', NumberType::class, ['required' => false]);
        $builder->add('email', TextType::class, ['required' => false]);
        $builder->add('giftMessage', TextType::class, ['required' => false]);
        $builder->add('isGuest', CheckboxType::class, ['required' => true]);
        $builder->add('shippingAddress', CartAddressApiType::class);
        $builder->add('billingAddress', CartAddressApiType::class);
        $builder->add('paymentDetails', TextType::class, ['required' => false]);
        $builder->add(
            'status',
            TranslatableEntityType::class,
            [
                'class' => 'OroMagentoBundle:CartStatus',
                'choice_label' => 'name'
            ]
        );
        $builder->add('notes', TextType::class, ['required' => false]);
        $builder->add('statusMessage', TextType::class, ['required' => false]);
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
        $builder->add('channel', IntegrationSelectType::class);
        $builder->add('originId', NumberType::class, ['required' => false]);

        $builder->addEventSubscriber(new PatchSubscriber());
        $builder->addEventSubscriber(new CartApiFormSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'      => 'Oro\Bundle\MagentoBundle\Entity\Cart',
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
