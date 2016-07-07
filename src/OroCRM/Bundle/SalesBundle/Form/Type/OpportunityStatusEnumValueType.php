<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Range;

use Oro\Bundle\EntityExtendBundle\Form\Type\EnumValueType;

/**
 * Extends EnumValueType to add a 'probability' field
 */
class OpportunityStatusEnumValueType extends EnumValueType
{
    const NAME = 'orocrm_sales_opportunity_status_enum_value';

    /**
     * @var array List of statuses which have non-editable probability
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
     * PRE_SET_DATA event handler
     * Add probability fields for each status
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $disabled = isset($data['id']) && in_array($data['id'], self::$immutableStatuses);

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
