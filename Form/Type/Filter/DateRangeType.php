<?php

namespace Oro\Bundle\GridBundle\Form\Type\Filter;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Sonata\AdminBundle\Form\Type\Filter\DateRangeType as SonataDateRangeType;

class DateRangeType extends SonataDateRangeType
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'oro_grid_type_filter_date_range';
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = array(
            self::TYPE_BETWEEN
                => $this->translator->trans('label_date_type_between', array(), 'SonataAdminBundle'),
            self::TYPE_NOT_BETWEEN
                => $this->translator->trans('label_date_type_not_between', array(), 'SonataAdminBundle'),
        );

        $options['field_options']['widget'] = 'single_text';
        $builder
            ->add('type', 'choice', array('choices' => $choices, 'required' => false))
            ->add('value', 'oro_grid_type_date_range', array('field_options' => $options['field_options']));
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'field_type'    => 'oro_grid_type_date_range',
                'field_options' => array('format' => 'yyyy-MM-dd')
            )
        );
    }
}
