<?php

namespace Oro\Bundle\AddressBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\UserBundle\Form\EventListener\PatchSubscriber;

class AddressApiType extends AddressType
{
    /**
     * {@inheritdoc}
     */
    public function addEntityFields(FormBuilderInterface $builder)
    {
        parent::addEntityFields($builder);
        $builder->addEventSubscriber(new PatchSubscriber());
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
                'type'          => $this->valueFormAlias,
                'property_path' => 'values',
                'allow_add'     => true,
                'allow_delete'  => true,
                'by_reference'  => false
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
