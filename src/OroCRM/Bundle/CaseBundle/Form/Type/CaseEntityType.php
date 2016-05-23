<?php

namespace OroCRM\Bundle\CaseBundle\Form\Type;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CaseEntityType extends AbstractType
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
                'oro_resizeable_rich_text',
                [
                    'label'        => 'orocrm.case.caseentity.description.label',
                    'required'     => false
                ]
            )
            ->add(
                'resolution',
                'oro_resizeable_rich_text',
                [
                    'label'        => 'orocrm.case.caseentity.resolution.label',
                    'required'     => false
                ]
            )
            ->add(
                'source',
                'entity',
                [
                    'label'        => 'orocrm.case.caseentity.source.label',
                    'class'        => 'OroCRMCaseBundle:CaseSource',
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
                'priority',
                'entity',
                [
                    'label'         => 'orocrm.case.caseentity.priority.label',
                    'class'         => 'OroCRMCaseBundle:CasePriority',
                    'query_builder' => function (EntityRepository $entityRepository) {
                        return $entityRepository->createQueryBuilder('priority')
                            ->orderBy('priority.order', 'ASC');
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
                'oro_user_organization_acl_select',
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
                'intention'          => 'orocrm_case_entity',
                'cascade_validation' => true
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_case_entity';
    }
}
