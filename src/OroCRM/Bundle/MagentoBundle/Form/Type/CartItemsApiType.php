<?php

namespace OroCRM\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\SoapBundle\Form\EventListener\PatchSubscriber;

use OroCRM\Bundle\MagentoBundle\Form\EventListener\CartItemApiFormSubscriber;

class CartItemsApiType extends AbstractType
{
    const NAME = 'cart_item_api_type';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('sku', 'text', ['required' => true]);
        $builder->add('name', 'text', ['required' => true]);
        $builder->add('qty', 'number', ['required' => true]);
        $builder->add('price', 'oro_money', ['required' => true]);
        $builder->add('discountAmount', 'oro_money', ['required' => true]);
        $builder->add('taxPercent', 'oro_percent', ['required' => true]);
        $builder->add('weight', 'number', ['required' => false]);
        $builder->add('productId', 'number', ['required' => true]);
        $builder->add('parentItemId', 'number', ['required' => false]);
        $builder->add('freeShipping', 'text', ['required' => true]);
        $builder->add('taxAmount', 'oro_money', ['required' => false]);
        $builder->add('giftMessage', 'text', ['required' => false]);
        $builder->add('taxClassId', 'text', ['required' => false]);
        $builder->add('description', 'text', ['required' => false]);
        $builder->add('isVirtual', 'checkbox', ['required' => true]);
        $builder->add('customPrice', 'oro_money', ['required' => false]);
        $builder->add('priceInclTax', 'oro_money', ['required' => false]);
        $builder->add('rowTotal', 'oro_money', ['required' => true]);
        $builder->add('productType', 'text', ['required' => true]);
        $builder->add('cart', 'orocrm_cart_select', ['required' => false]);

        $builder->addEventSubscriber(new PatchSubscriber());
        $builder->addEventSubscriber(new CartItemApiFormSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'           => 'OroCRM\Bundle\MagentoBundle\Entity\CartItem',
                'intention'            => 'items',
                'extra_fields_message' => 'This form should not contain extra fields: "{{ extra_fields }}"',
                'single_form'          => true,
                'csrf_protection'      => false
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
