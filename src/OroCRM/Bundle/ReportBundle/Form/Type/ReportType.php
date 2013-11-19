<?php

namespace OroCRM\Bundle\ReportBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use OroCRM\Bundle\ReportBundle\Entity\Report;

class ReportType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('required' => true))
            ->add('entity', 'orocrm_report_entity_choice', array('required' => true))
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
            ->add('definition', 'hidden', array('required' => false));

        $factory = $builder->getFormFactory();
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($factory) {
                $form = $event->getForm();
                /** @var Report $data */
                $data = $event->getData();

                $form->add(
                    $factory->createNamed(
                        'column',
                        'oro_query_designer_column',
                        null,
                        array(
                            'mapped'             => false,
                            'column_choice_type' => 'orocrm_report_entity_field_choice',
                            'entity'             => $data ? $data->getEntity() : null,
                            'auto_initialize'    => false
                        )
                    )
                );
                $form->add(
                    $factory->createNamed(
                        'filter',
                        'oro_query_designer_filter',
                        null,
                        array(
                            'mapped'             => false,
                            'column_choice_type' => 'orocrm_report_entity_field_choice',
                            'entity'             => $data ? $data->getEntity() : null,
                            'auto_initialize'    => false
                        )
                    )
                );
            }
        );
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
