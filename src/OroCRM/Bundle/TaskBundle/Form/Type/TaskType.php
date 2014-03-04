<?php

namespace OroCRM\Bundle\TaskBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'subject',
                'text',
                [
                    'required' => true,
                    'label' => 'orocrm.task.subject.label'
                ]
            )
            ->add(
                'description',
                'textarea',
                [
                    'required' => false,
                    'label' => 'orocrm.task.description.label'
                ]
            )
            ->add(
                'dueDate',
                'oro_datetime',
                [
                    'required' => true,
                    'label' => 'orocrm.task.dueDate.label'
                ]
            )
            ->add(
                'priority',
                'entity',
                [
                    'label' => 'orocrm.task.priority.label',
                    'class' => 'OroCRM\Bundle\TaskBundle\Entity\TaskPriority',
                    'required' => true
                ]
            )
            ->add(
                'assignedTo',
                'oro_user_select',
                [
                    'required' => true,
                    'label' => 'orocrm.task.assignedTo.label'
                ]
            )
            ->add(
                'relatedAccount',
                'orocrm_account_select',
                [
                    'required' => false,
                    'label' => 'orocrm.task.relatedAccount.label'
                ]
            )
            ->add(
                'relatedContact',
                'orocrm_contact_select',
                [
                    'required' => false,
                    'label' => 'orocrm.task.relatedContact.label'
                ]
            )
            ->add(
                'owner',
                'oro_user_select',
                [
                    'required' => true,
                    'label' => 'orocrm.task.owner.label'
                ]
            );
    }

    public function getName()
    {
        return 'orocrm_task';
    }
} 