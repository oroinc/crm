<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Range;

use Oro\Bundle\EntityExtendBundle\Form\Type\EnumValueType;

/**
 * Extends EnumValueType to add 'probability'
 */
class OpportunityStatusEnumValueType extends EnumValueType
{
    const NAME = 'orocrm_sales_opportunity_status_enum_value';

    /**
     * @var array Default probability for these statuses cannot be edited
     */
    public static $immutableStatuses = ['won', 'lost'];

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
        $disabled = in_array($event->getData()['id'], self::$immutableStatuses);
        $form = $event->getForm();
        $form->add(
            'probability',
            'oro_percent',
            [
                'disabled' => $disabled,
                'attr' => ['readonly' => $disabled],
                'constraints' => new Range(['min' => 0, 'max' => 100]),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
