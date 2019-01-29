<?php

namespace Oro\Bundle\MagentoBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;
use Oro\Bundle\FormBundle\Form\Type\OroPercentType;
use Oro\Bundle\MagentoBundle\Form\EventListener\CartItemApiFormSubscriber;
use Oro\Bundle\SoapBundle\Form\EventListener\PatchSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CartItemsApiType extends AbstractType
{
    const NAME = 'cart_item_api_type';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('sku', TextType::class, ['required' => false]);
        $builder->add('name', TextType::class, ['required' => true]);
        $builder->add('qty', NumberType::class, ['required' => true]);
        $builder->add('price', OroMoneyType::class, ['required' => true]);
        $builder->add('discountAmount', OroMoneyType::class, ['required' => true]);
        $builder->add('taxPercent', OroPercentType::class, ['required' => true]);
        $builder->add('weight', NumberType::class, ['required' => false]);
        $builder->add('productId', NumberType::class, ['required' => true]);
        $builder->add('parentItemId', NumberType::class, ['required' => false]);
        $builder->add('freeShipping', TextType::class, ['required' => true]);
        $builder->add('taxAmount', OroMoneyType::class, ['required' => false]);
        $builder->add('giftMessage', TextType::class, ['required' => false]);
        $builder->add('taxClassId', TextType::class, ['required' => false]);
        $builder->add('description', TextType::class, ['required' => false]);
        $builder->add('isVirtual', CheckboxType::class, ['required' => true]);
        $builder->add('customPrice', OroMoneyType::class, ['required' => false]);
        $builder->add('priceInclTax', OroMoneyType::class, ['required' => false]);
        $builder->add('rowTotal', OroMoneyType::class, ['required' => true]);
        $builder->add('productType', TextType::class, ['required' => true]);
        $builder->add('cart', CartSelectType::class, ['required' => false]);

        $builder->addEventSubscriber(new PatchSubscriber());
        $builder->addEventSubscriber(new CartItemApiFormSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'           => 'Oro\Bundle\MagentoBundle\Entity\CartItem',
                'csrf_token_id'        => 'items',
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
