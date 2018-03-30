<?php

namespace Oro\Bundle\CaseBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\AccountBundle\Form\Type\AccountSelectType;
use Oro\Bundle\ContactBundle\Form\Type\ContactSelectType;
use Oro\Bundle\FormBundle\Form\Type\OroResizeableRichTextType;
use Oro\Bundle\UserBundle\Form\Type\OrganizationUserAclSelectType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
                TextType::class,
                [
                    'label'        => 'oro.case.caseentity.subject.label'
                ]
            )
            ->add(
                'description',
                OroResizeableRichTextType::class,
                [
                    'label'        => 'oro.case.caseentity.description.label',
                    'required'     => false
                ]
            )
            ->add(
                'resolution',
                OroResizeableRichTextType::class,
                [
                    'label'        => 'oro.case.caseentity.resolution.label',
                    'required'     => false
                ]
            )
            ->add(
                'source',
                EntityType::class,
                [
                    'label'        => 'oro.case.caseentity.source.label',
                    'class'        => 'OroCaseBundle:CaseSource',
                ]
            )
            ->add(
                'status',
                EntityType::class,
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
                EntityType::class,
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
                ContactSelectType::class,
                [
                    'required'      => false,
                    'label'         => 'oro.case.caseentity.related_contact.label',
                ]
            )
            ->add(
                'relatedAccount',
                AccountSelectType::class,
                [
                    'required'      => false,
                    'label'         => 'oro.case.caseentity.related_account.label',
                ]
            )
            ->add(
                'assignedTo',
                OrganizationUserAclSelectType::class,
                [
                    'required'      => false,
                    'label'         => 'oro.case.caseentity.assigned_to.label',
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => 'Oro\\Bundle\\CaseBundle\\Entity\\CaseEntity',
                'csrf_token_id'      => 'oro_case_entity',
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
