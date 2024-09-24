<?php

namespace Oro\Bundle\CaseBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\CaseBundle\Entity\CaseMailboxProcessSettings;
use Oro\Bundle\CaseBundle\Entity\CasePriority;
use Oro\Bundle\CaseBundle\Entity\CaseStatus;
use Oro\Bundle\EmailBundle\Entity\Mailbox;
use Oro\Bundle\UserBundle\Form\Type\OrganizationUserAclSelectType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * Case mailbox process settings form type
 */
class CaseMailboxProcessSettingsType extends AbstractType
{
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'oro_case_mailbox_process_settings';
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Oro\Bundle\CaseBundle\Entity\CaseMailboxProcessSettings',
        ]);
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'owner',
            OrganizationUserAclSelectType::class,
            [
                'required'    => true,
                'label'       => 'oro.case.caseentity.owner.label',
                'constraints' => [
                    new NotNull(),
                ],
            ]
        )->add(
            'assignTo',
            OrganizationUserAclSelectType::class,
            [
                'required' => false,
                'label'    => 'oro.case.caseentity.assigned_to.label',
            ]
        )->add(
            'status',
            EntityType::class,
            [
                'label'         => 'oro.case.caseentity.status.label',
                'class'         => CaseStatus::class,
                'query_builder' => function (EntityRepository $entityRepository) {
                    return $entityRepository->createQueryBuilder('status')
                        ->orderBy('status.order', 'ASC');
                },
                'constraints'   => [
                    new NotNull(),
                ]
            ]
        )->add(
            'priority',
            EntityType::class,
            [
                'label'         => 'oro.case.caseentity.priority.label',
                'class'         => CasePriority::class,
                'query_builder' => function (EntityRepository $entityRepository) {
                    return $entityRepository->createQueryBuilder('priority')
                        ->orderBy('priority.order', 'ASC');
                },
                'constraints'   => [
                    new NotNull(),
                ]
            ]
        )->add(
            'tags',
            CaseMailboxProcessSettingsTagType::class,
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
