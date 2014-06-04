<?php

namespace OroCRM\Bundle\CaseBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\SoapBundle\Form\EventListener\PatchSubscriber;

class CaseApiType extends BaseCaseType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add(
                'reportedAt',
                'oro_datetime',
                [
                    'required' => true,
                    'label'    => 'orocrm.case.reportedAt.label'
                ]
            )
            ->add(
                'closedAt',
                'oro_datetime',
                [
                    'required' => true,
                    'label'    => 'orocrm.case.closedAt.label'
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
                'data_class'         => 'OroCRM\Bundle\CaseBundle\Entity\CaseEntity',
                'intention'          => 'case',
                'cascade_validation' => true,
                'csrf_protection'    => false
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'case';
    }
}
