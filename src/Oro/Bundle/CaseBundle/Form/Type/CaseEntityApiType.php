<?php

namespace Oro\Bundle\CaseBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;

use Oro\Bundle\SoapBundle\Form\EventListener\PatchSubscriber;

class CaseEntityApiType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'reportedAt',
                'oro_datetime',
                [
                    'required' => true,
                    'label'    => 'oro.case.caseentity.reported_at.label'
                ]
            )
            ->add(
                'closedAt',
                'oro_datetime',
                [
                    'required' => true,
                    'label'    => 'oro.case.caseentity.closed_at.label'
                ]
            );

        $builder->addEventSubscriber(new PatchSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'csrf_protection' => false
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oro_case_entity_api';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_case_entity';
    }
}
