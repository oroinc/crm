<?php

namespace Oro\Bundle\CaseBundle\Form\Type;

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
                    'label'        => 'oro.case.caseentity.subject.label'
                ]
            )
            ->add(
                'description',
                'oro_resizeable_rich_text',
                [
                    'label'        => 'oro.case.caseentity.description.label',
                    'required'     => false
                ]
            )
            ->add(
                'resolution',
                'oro_resizeable_rich_text',
                [
                    'label'        => 'oro.case.caseentity.resolution.label',
                    'required'     => false
                ]
            )
            ->add(
                'source',
                'entity',
                [
                    'label'        => 'oro.case.caseentity.source.label',
                    'class'        => 'OroCaseBundle:CaseSource',
                ]
            )
            ->add(
                'status',
                'entity',
                [
                    'label'         => 'oro.case.caseentity.status.label',
                    'class'         => 'OroCaseBundle:CaseStatus',
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
                    'label'         => 'oro.case.caseentity.priority.label',
                    'class'         => 'OroCaseBundle:CasePriority',
                    'query_builder' => function (EntityRepository $entityRepository) {
                        return $entityRepository->createQueryBuilder('priority')
                            ->orderBy('priority.order', 'ASC');
                    }
                ]
            )
            ->add(
                'relatedContact',
                'oro_contact_select',
                [
                    'required'      => false,
                    'label'         => 'oro.case.caseentity.related_contact.label',
                ]
            )
            ->add(
                'relatedAccount',
                'oro_account_select',
                [
                    'required'      => false,
                    'label'         => 'oro.case.caseentity.related_account.label',
                ]
            )
            ->add(
                'assignedTo',
                'oro_user_organization_acl_select',
                [
                    'required'      => false,
                    'label'         => 'oro.case.caseentity.assigned_to.label',
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
                'data_class'         => 'Oro\\Bundle\\CaseBundle\\Entity\\CaseEntity',
                'intention'          => 'oro_case_entity',
            ]
        );
    }

    /**
     * {@inheritdoc}
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
        return 'oro_case_entity';
    }
}
