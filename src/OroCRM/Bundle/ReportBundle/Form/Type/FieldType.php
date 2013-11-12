<?php

namespace OroCRM\Bundle\ReportBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;

class FieldType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'oro_entity_field_choice', array('required' => true))
            ->add('label', 'text', array('required' => true))
            ->add(
                'sorting',
                'choice',
                array(
                    'required'    => false,
                    'choices'     => array(
                        'ASC'  => 'orocrm.report.form.field_sorting_asc',
                        'DESC' => 'orocrm.report.form.field_sorting_desc'
                    ),
                    'empty_value' => 'orocrm.report.form.choose_field_sorting'
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
                'data_class'         => 'OroCRM\Bundle\ReportBundle\Form\Model\Field',
                'intention'          => 'report_field'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_report_field';
    }
}
