<?php

namespace OroCRM\Bundle\TaskBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\SoapBundle\Form\EventListener\PatchSubscriber;

class TaskApiType extends TaskType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add(
            'createdAt',
            'oro_datetime',
            [
                'required' => false,
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
                'data_class' => 'OroCRM\Bundle\TaskBundle\Entity\Task',
                'intention' => 'task',
                'cascade_validation' => true,
                'csrf_protection' => false
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'task';
    }

    /**
     * @param FormBuilderInterface $builder
     */
    protected function addDueDateField(FormBuilderInterface $builder)
    {
        // no any additional constraints for "dueDate" in API
        $builder
            ->add(
                'dueDate',
                'oro_datetime',
                ['required' => false]
            );
    }

    /**
     * @param FormEvent $event
     */
    protected function updateDueDateFieldConstraints(FormEvent $event)
    {
        // no any additional constraints for "dueDate" in API
    }
}
