<?php

namespace Oro\Bundle\CaseBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroDateTimeType;
use Oro\Bundle\SoapBundle\Form\EventListener\PatchSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
                OroDateTimeType::class,
                [
                    'required' => true,
                    'label'    => 'oro.case.caseentity.reported_at.label'
                ]
            )
            ->add(
                'closedAt',
                OroDateTimeType::class,
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
    public function configureOptions(OptionsResolver $resolver)
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
        return CaseEntityType::class;
    }
}
