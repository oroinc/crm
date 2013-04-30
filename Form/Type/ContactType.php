<?php

namespace Oro\Bundle\ContactBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\FlexibleEntityBundle\Form\Type\FlexibleType;
use Oro\Bundle\FlexibleEntityBundle\Form\Type\FlexibleValueType;

class ContactType extends FlexibleType
{
    /**
     * {@inheritdoc}
     */
    public function addEntityFields(FormBuilderInterface $builder)
    {
        // add default flexible fields
        parent::addEntityFields($builder);

        // contact fields
        $builder->add(
            'name',
            'text',
            array(
                'label' => 'Name',
                'required' => true,
            )
        );
    }

    /**
     * Add entity fields to form builder
     *
     * @param FormBuilderInterface $builder
     */
    public function addDynamicAttributesFields(FormBuilderInterface $builder)
    {
        $builder->add(
            'attributes',
            'collection',
            array(
                'type' => new FlexibleValueType($this->valueClass),
                'property_path' => 'values',
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => $this->flexibleClass,
                'intention' => 'account',
                'extra_fields_message' => 'This form should not contain extra fields: "{{ extra_fields }}"',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_contact';
    }
}
