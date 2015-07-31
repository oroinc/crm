<?php

namespace OroCRM\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\SoapBundle\Form\EventListener\PatchSubscriber;

use Symfony\Component\Validator\Constraints as Assert;

class OrderItemsApiType extends OrderItemType
{
    const NAME = 'order_item_api_type';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', ['required' => false]);
        $builder->add('sku', 'text', ['required' => false]);
        $builder->add('qty', 'number', ['required' => false]);
        $builder->add('cost', 'oro_money', ['required' => false]);
        $builder->add('price', 'oro_money', ['required' => false]);
        $builder->add('weight', 'number', ['required' => false]);
        $builder->add('taxPercent', 'oro_percent', ['required' => false]);
        $builder->add('taxAmount', 'oro_money', ['required' => false]);
        $builder->add('discountPercent', 'oro_percent', ['required' => false]);
        $builder->add('discountAmount', 'oro_money', ['required' => false]);
        $builder->add('rowTotal', 'oro_money', ['required' => false]);
        $builder->add('order', 'orocrm_order_select');
        $builder->add('productType', 'text', ['required' => false]);
        $builder->add('productOptions', 'text', ['required' => false]);
        $builder->add('isVirtual', 'checkbox', ['required' => false]);
        $builder->add('originalPrice', 'oro_money', ['required' => false]);

        $builder->addEventSubscriber(new PatchSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'      => 'OroCRM\Bundle\MagentoBundle\Entity\OrderItem',
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
