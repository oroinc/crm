<?php
namespace Oro\Bundle\FlexibleEntityBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

class PhoneCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('phones', 'collection', array(
            'type'           => new PhoneType(),
            'allow_add'      => true,
            'allow_delete'   => true,
            'by_reference'   => false,
            'prototype'      => true,
            'prototype_name' => 'tag__name__',
            'label'          => ' '
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Oro\Bundle\FlexibleEntityBundle\Entity\Phone'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_flexibleentity_phone_collection';
    }
}
