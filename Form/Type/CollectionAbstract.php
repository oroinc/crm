<?php

namespace Oro\Bundle\FlexibleEntityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

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
}
