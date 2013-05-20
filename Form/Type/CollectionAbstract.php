<?php

namespace Oro\Bundle\FlexibleEntityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

use Oro\Bundle\FlexibleEntityBundle\Form\EventListener\CollectionTypeSubscriber;

abstract class CollectionAbstract extends AbstractType
{
    /**
     * @var CollectionTypeSubscriber
     */
    protected $eventListener;

    /**
     * @param CollectionTypeSubscriber $eventListener
     */
    public function __construct(CollectionTypeSubscriber $eventListener)
    {
        $this->eventListener = $eventListener;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this->eventListener);
    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public function finishView(FormView $view, FormInterface $form, array $options)
//    {
//        $view->vars['preset_fields_count'] = $form->getAttribute('preset_fields_count') ?: 1;
//    }
}
