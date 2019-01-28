<?php

namespace Oro\Bundle\MagentoBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;
use Oro\Bundle\FormBundle\Form\Type\OroPercentType;
use Oro\Bundle\SoapBundle\Form\EventListener\PatchSubscriber;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderItemsApiType extends OrderItemType
{
    const NAME = 'order_item_api_type';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, ['required' => false]);
        $builder->add('sku', TextType::class, ['required' => false]);
        $builder->add('qty', NumberType::class, ['required' => false]);
        $builder->add('cost', OroMoneyType::class, ['required' => false]);
        $builder->add('price', OroMoneyType::class, ['required' => false]);
        $builder->add('weight', NumberType::class, ['required' => false]);
        $builder->add('taxPercent', OroPercentType::class, ['required' => false]);
        $builder->add('taxAmount', OroMoneyType::class, ['required' => false]);
        $builder->add('discountPercent', OroPercentType::class, ['required' => false]);
        $builder->add('discountAmount', OroMoneyType::class, ['required' => false]);
        $builder->add('rowTotal', OroMoneyType::class, ['required' => false]);
        $builder->add('order', OrderSelectType::class);
        $builder->add('productType', TextType::class, ['required' => false]);
        $builder->add('productOptions', TextType::class, ['required' => false]);
        $builder->add('isVirtual', CheckboxType::class, ['required' => false]);
        $builder->add('originalPrice', OroMoneyType::class, ['required' => false]);

        $builder->addEventSubscriber(new PatchSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'      => 'Oro\Bundle\MagentoBundle\Entity\OrderItem',
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
