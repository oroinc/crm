<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Oro\Bundle\EntityExtendBundle\Form\Type\EnumValueType;
use Oro\Bundle\FormBundle\Form\Type\OroPercentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Range;

/**
 * Extends EnumValueType to add a 'probability' field
 */
class OpportunityStatusEnumValueType extends AbstractType
{
    const NAME = 'oro_sales_opportunity_status_enum_value';

    /**
     * @var array List of statuses which have non-editable probability
     */
    public static $immutableProbabilityStatuses = ['won', 'lost'];

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'preSetData']);
    }

    /**
     * PRE_SET_DATA event handler
     * Add probability fields for each status
     * We do it in the listener to disable fields dynamically
     */
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $disabled = isset($data['id']) && in_array($data['id'], self::$immutableProbabilityStatuses);

        $form = $event->getForm();
        $attr = [];

        if ($disabled) {
            $attr['readonly'] = true;
        }

        $form->add(
            'probability',
            OroPercentType::class,
            [
                'disabled' => $disabled,
                'attr' => $attr,
                'constraints' => new Range(['min' => 0, 'max' => 100]),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return EnumValueType::class;
    }

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
        return self::NAME;
    }
}
