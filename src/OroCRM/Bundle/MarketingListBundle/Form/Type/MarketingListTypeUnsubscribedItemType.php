<?php

namespace OroCRM\Bundle\MarketingListBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MarketingListTypeUnsubscribedItemType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('entityId', 'integer', ['required' => true])
            ->add(
                'marketingList',
                'entity',
                [
                    'class'    => 'OroCRM\Bundle\MarketingListBundle\Entity\MarketingList',
                    'required' => true
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => 'OroCRM\Bundle\MarketingListBundle\Entity\MarketingListUnsubscribedItem',
                'intention'          => 'marketing_list_unsubscribed_item',
                'cascade_validation' => true
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_marketing_list_unsubscribed_item';
    }
}
