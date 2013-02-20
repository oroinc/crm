<?php

namespace Oro\Bundle\GridBundle\Form\Type\Filter\Flexible;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OptionsType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'oro_grid_type_filter_flexible_options';
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'type',
                $options['operator_type'],
                array_merge(array('required' => false), $options['operator_options'])
            )
            ->add(
                'value',
                'choice',
                array_merge(array('choices' => array(), 'required' => false), $options['field_options'])
            );
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'operator_type'    => 'hidden',
                'operator_options' => array(),
                'field_type'       => 'choice',
                'field_options'    => array()
            )
        );
    }
}
