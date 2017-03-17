<?php

namespace Oro\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OrderItemType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', ['required' => false]);
        $builder->add('sku', 'text', ['required' => false]);
        $builder->add('qty', 'oro_money', ['required' => false]);
        $builder->add('cost', 'oro_money', ['required' => false]);
        $builder->add('price', 'oro_money', ['required' => false]);
        $builder->add('weight', 'number', ['required' => false]);
        $builder->add('taxPercent', 'oro_percent', ['required' => false]);
        $builder->add('taxAmount', 'oro_money', ['required' => false]);
        $builder->add('discountPercent', 'oro_percent', ['required' => false]);
        $builder->add('discountAmount', 'oro_money', ['required' => false]);
        $builder->add('rowTotal', 'oro_money', ['required' => false]);
        $builder->add('productType', 'text', ['required' => false]);
        $builder->add('productOptions', 'text', ['required' => false]);
        $builder->add('isVirtual', 'checkbox', ['required' => false]);
        $builder->add('originalPrice', 'oro_money', ['required' => false]);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'           => 'Oro\Bundle\MagentoBundle\Entity\OrderItem',
                'intention'            => 'items',
                'single_form'          => true
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
        return 'oro_order_item';
    }
}
