<?php

namespace Oro\Bundle\CaseBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroDateTimeType;
use Oro\Bundle\SoapBundle\Form\EventListener\PatchSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Defines the form type for case entity data input in REST API endpoints.
 */
class CaseEntityApiType extends AbstractType
{
    #[\Override]
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

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'csrf_protection' => false
            ]
        );
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'oro_case_entity_api';
    }

    #[\Override]
    public function getParent(): ?string
    {
        return CaseEntityType::class;
    }
}
