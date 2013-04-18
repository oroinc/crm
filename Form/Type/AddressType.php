<?php

namespace Oro\Bundle\AddressBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Oro\Bundle\FlexibleEntityBundle\Form\Type\FlexibleType;

class AddressType extends FlexibleType
{
    /**
     * {@inheritdoc}
     */
    public function addEntityFields(FormBuilderInterface $builder)
    {
        // add default flexible fields
        parent::addEntityFields($builder);

        // address fields
        $builder
            ->add('street', 'text');
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'           => $this->flexibleClass,
            'intention'            => 'address',
            'extra_fields_message' => 'This form should not contain extra fields: "{{ extra_fields }}"',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_address';
    }
}
