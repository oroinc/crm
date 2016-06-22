<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

use Oro\Bundle\EntityExtendBundle\Form\Type\EnumValueType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Extends EnumValueType to add 'probability'
 */
class OpportunityStatusEnumValueType extends EnumValueType
{
    const NAME = 'orocrm_sales_opportunity_status_enum_value';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'preSetData']);
    }

    /**
     * Pre set data event handler
     * Populate probability fields from the System Config (scoped)
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $form->add('probability', 'percent', ['disabled' => in_array($event->getData()['id'], ['lost', 'won'])]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
