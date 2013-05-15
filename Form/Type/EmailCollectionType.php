<?php
namespace Oro\Bundle\FlexibleEntityBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

class EmailCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('emails', 'collection', array(
            'type'           => new EmailType(),
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
    public function getName()
    {
        return 'oro_flexibleentity_email_collection';
    }
}
