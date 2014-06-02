<?php

namespace OroCRM\Bundle\CaseBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CaseReporterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'user',
                'oro_user_select',
                [
                    'required' => false,
                    'label'    => 'orocrm.case.reporter.user.label',
                ]
            )
            ->add(
                'contact',
                'orocrm_contact_select',
                [
                    'required' => false,
                    'label'    => 'orocrm.case.reporter.contact.label',
                ]
            )
            ->add(
                'customer',
                'entity',
                [
                    'label'    => 'orocrm.case.reporter.customer.label',
                    'class'    => 'OroCRMMagentoBundle:Customer',
                    'property' => 'email',
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
                'data_class'         => 'OroCRM\Bundle\CaseBundle\Entity\CaseReporter',
                'intention'          => 'case_reporter',
                'cascade_validation' => true
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_case_reporter';
    }
}
