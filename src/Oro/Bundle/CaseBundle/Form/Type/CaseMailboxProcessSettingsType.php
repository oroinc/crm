<?php

namespace Oro\Bundle\CaseBundle\Form\Type;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

use Oro\Bundle\EmailBundle\Entity\Mailbox;
use Oro\Bundle\CaseBundle\Entity\CaseMailboxProcessSettings;
use Oro\Bundle\CaseBundle\Entity\CaseStatus;
use Oro\Bundle\CaseBundle\Entity\CasePriority;

class CaseMailboxProcessSettingsType extends AbstractType
{
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
        return 'oro_case_mailbox_process_settings';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Oro\Bundle\CaseBundle\Entity\CaseMailboxProcessSettings',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'owner',
            'oro_user_organization_acl_select',
            [
                'required'    => true,
                'label'       => 'oro.case.caseentity.owner.label',
                'constraints' => [
                    new NotNull(),
                ],
            ]
        )->add(
            'assignTo',
            'oro_user_organization_acl_select',
            [
                'required' => false,
                'label'    => 'oro.case.caseentity.assigned_to.label',
            ]
        )->add(
            'status',
            'entity',
            [
                'label'         => 'oro.case.caseentity.status.label',
                'class'         => 'OroCaseBundle:CaseStatus',
                'query_builder' => function (EntityRepository $entityRepository) {
                    return $entityRepository->createQueryBuilder('status')
                        ->orderBy('status.order', 'ASC');
                },
                'constraints'   => [
                    new NotNull(),
                ],
                'preferred_choices' => function (CaseStatus $entity) {
                    return $entity->getName() === CaseStatus::STATUS_OPEN;
                },
            ]
        )->add(
            'priority',
            'entity',
            [
                'label'         => 'oro.case.caseentity.priority.label',
                'class'         => 'OroCaseBundle:CasePriority',
                'query_builder' => function (EntityRepository $entityRepository) {
                    return $entityRepository->createQueryBuilder('priority')
                        ->orderBy('priority.order', 'ASC');
                },
                'constraints'   => [
                    new NotNull(),
                ],
                'preferred_choices' => function (CasePriority $entity) {
                    return $entity->getName() === CasePriority::PRIORITY_LOW;
                },
            ]
        )->add(
            'tags',
            'case_mailbox_process_settings_tag',
            [
                'label' => 'oro.tag.entity_plural_label',
            ]
        );

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            if ($event->getData()) {
                return;
            }

            $mailbox = $event->getForm()->getRoot()->getData();
            if (!$mailbox instanceof Mailbox) {
                return;
            }

            $processSettings = new CaseMailboxProcessSettings();
            $mailbox->setProcessSettings($processSettings);
            $event->setData($processSettings);
        });
    }
}
