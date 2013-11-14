<?php

namespace OroCRM\Bundle\ReportBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;

class ReportType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('required' => true))
            ->add('entity', 'oro_entity_choice', array('required' => true))
            ->add(
                'type',
                'entity',
                array(
                    'class'       => 'OroCRMReportBundle:ReportType',
                    'property'    => 'label',
                    'required'    => true,
                    'empty_value' => 'orocrm.report.form.choose_report_type'
                )
            )
            ->add('description', 'textarea', array('required' => false))
            ->add('definition', 'hidden', array('required' => false))
            ->add('column', 'oro_query_designer_column', array('mapped' => false))
            ->add('filter', 'oro_query_designer_filter', array('mapped' => false));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'         => 'OroCRM\Bundle\ReportBundle\Entity\Report',
                'intention'          => 'report',
                'cascade_validation' => true,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_report';
    }
}
