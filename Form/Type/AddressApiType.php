<?php

namespace Oro\Bundle\AddressBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Oro\Bundle\FlexibleEntityBundle\Form\Type\FlexibleType;


class AddressApiType extends FlexibleType
{
    /**
     * {@inheritdoc}
     */
    public function addEntityFields(FormBuilderInterface $builder)
    {
        // add default flexible fields
        parent::addEntityFields($builder);

        $required =  array(
            'required' => true,
        );
        $notRequired =  array(
            'required' => false,
        );

        // address fields
        $builder
            ->add('street', 'text', $required)
            ->add('street2', 'text', $notRequired)
            ->add('city', 'text', $required)
            ->add('state', 'text', $required)
            ->add('postal_code', 'text', $required)
            ->add('country', 'text', $required)
            ->add('mark', 'text', $notRequired);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(
            array(
                'data_class'           => $this->flexibleClass,
                'intention'            => 'address',
                'extra_fields_message' => 'This form should not contain extra fields: "{{ extra_fields }}"',
                'csrf_protection'      => false,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'address';
    }
}
