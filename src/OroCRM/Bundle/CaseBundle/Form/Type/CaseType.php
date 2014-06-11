<?php

namespace OroCRM\Bundle\CaseBundle\Form\Type;

use Doctrine\ORM\EntityRepository;

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
                    'label'        => 'orocrm.case.caseentity.subject.label'
                ]
            )
            ->add(
                'description',
                'textarea',
                [
                    'label'        => 'orocrm.case.caseentity.description.label',
                    'required'     => false
                ]
            )
            ->add(
                'origin',
                'entity',
                [
                    'label'        => 'orocrm.case.caseentity.origin.label',
                    'class'        => 'OroCRMCaseBundle:CaseOrigin',
                ]
            )
            ->add(
                'status',
                'entity',
                [
                    'label'         => 'orocrm.case.caseentity.status.label',
                    'class'         => 'OroCRMCaseBundle:CaseStatus',
                    'query_builder' => function (EntityRepository $entityRepository) {
                        return $entityRepository->createQueryBuilder('status')
                            ->orderBy('status.order', 'ASC');
                    }
                ]
            )
            ->add(
                'relatedContact',
                'orocrm_contact_select',
                [
                    'required'      => false,
                    'label'         => 'orocrm.case.caseentity.related_contact.label',
                ]
            )
            ->add(
                'relatedAccount',
                'orocrm_account_select',
                [
                    'required'      => false,
                    'label'         => 'orocrm.case.caseentity.related_account.label',
                ]
            )
            ->add(
                'assignedTo',
                'oro_user_select',
                [
                    'required'      => false,
                    'label'         => 'orocrm.case.caseentity.assigned_to.label',
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
                'data_class'         => 'OroCRM\\Bundle\\CaseBundle\\Entity\\CaseEntity',
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
