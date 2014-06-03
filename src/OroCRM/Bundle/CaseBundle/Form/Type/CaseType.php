<?php

namespace OroCRM\Bundle\CaseBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CaseType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'subject',
                'text',
                [
                    'label' => 'orocrm.case.subject.label'
                ]
            )
            ->add(
                'description',
                'textarea',
                [
                    'label' => 'orocrm.case.description.label'
                ]
            )
            ->add(
                'owner',
                'oro_user_select',
                [
                    'label' => 'orocrm.case.owner.label'
                ]
            )
            ->add(
                'reporter',
                'orocrm_case_reporter',
                [
                    'label' => 'orocrm.case.reporter.label'
                ]
            )
            ->add(
                'item',
                'orocrm_case_item',
                [
                    'label' => 'orocrm.case.item.label'
                ]
            )
            ->add(
                'origin',
                'entity',
                [
                    'label'    => 'orocrm.case.origins.label',
                    'class'    => 'OroCRMCaseBundle:CaseOrigin',
                    'property' => 'code',
                    'required' => false,
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => 'OroCRM\Bundle\CaseBundle\Entity\CaseEntity',
                'intention'          => 'case',
                'cascade_validation' => true
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_case';
    }
}
