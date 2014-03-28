<?php

namespace OroCRM\Bundle\TaskBundle\Form\Type;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TaskType extends AbstractType
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
                    'label' => 'orocrm.task.due_date.label'
                ]
            )
            ->add(
                'taskPriority',
                'entity',
                [
                    'label' => 'orocrm.task.task_priority.label',
                    'class' => 'OroCRM\Bundle\TaskBundle\Entity\TaskPriority',
                    'required' => true,
                    'query_builder' => function (EntityRepository $repository) {
                        return $repository->createQueryBuilder('priority')->orderBy('priority.order');
                    }
                ]
            )
            ->add(
                'relatedAccount',
                'orocrm_account_select',
                [
                    'required' => false,
                    'label' => 'orocrm.task.related_account.label'
                ]
            )
            ->add(
                'relatedContact',
                'orocrm_contact_select',
                [
                    'required' => false,
                    'label' => 'orocrm.task.related_contact.label'
                ]
            )
            ->add(
                'owner',
                'oro_user_select',
                [
                    'required' => true,
                    'label' => 'orocrm.task.assigned_to.label'
                ]
            )
            ->add(
                'reporter',
                'oro_user_select',
                [
                    'required' => true,
                    'label' => 'orocrm.task.reporter.label'
                ]
            )
            ->add(
                'reminders',
                'oro_reminder_collection',
                [
                    'required' => false,
                    'label' => 'oro.reminder.entity_plural_label'
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
                'data_class' => 'OroCRM\Bundle\TaskBundle\Entity\Task',
                'intention' => 'task',
                'cascade_validation' => true
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_task';
    }
}
