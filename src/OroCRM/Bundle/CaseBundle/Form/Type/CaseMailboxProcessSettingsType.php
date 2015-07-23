<?php

namespace OroCRM\Bundle\CaseBundle\Form\Type;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotNull;

class CaseMailboxProcessSettingsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_case_mailbox_process_settings';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'OroCRM\Bundle\CaseBundle\Entity\CaseMailboxProcessorSettings',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'owner',
            'oro_user_select',
            [
                'required'    => true,
                'label'       => 'orocrm.case.caseentity.owner.label',
                'constraints' => [
                    new NotNull(),
                ],
            ]
        )->add(
            'assignTo',
            'oro_user_organization_acl_select',
            [
                'required' => false,
                'label'    => 'orocrm.case.caseentity.assigned_to.label',
            ]
        )->add(
            'status',
            'entity',
            [
                'label'         => 'orocrm.case.caseentity.status.label',
                'class'         => 'OroCRMCaseBundle:CaseStatus',
                'query_builder' => function (EntityRepository $entityRepository) {
                    return $entityRepository->createQueryBuilder('status')
                        ->orderBy('status.order', 'ASC');
                },
                'constraints'   => [
                    new NotNull(),
                ],
            ]
        )->add(
            'priority',
            'entity',
            [
                'label'         => 'orocrm.case.caseentity.priority.label',
                'class'         => 'OroCRMCaseBundle:CasePriority',
                'query_builder' => function (EntityRepository $entityRepository) {
                    return $entityRepository->createQueryBuilder('priority')
                        ->orderBy('priority.order', 'ASC');
                },
                'constraints'   => [
                    new NotNull(),
                ],
            ]
        )->add(
            'tags',
            'oro_tag_select',
            [
                'label' => 'oro.tag.entity_plural_label'
            ]
        );
    }
}
