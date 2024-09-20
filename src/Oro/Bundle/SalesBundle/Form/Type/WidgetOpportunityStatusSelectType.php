<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Oro\Bundle\EntityExtendBundle\Provider\EnumOptionsProvider;
use Oro\Bundle\FormBundle\Form\Type\Select2ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type provides functionality to select an existing entity from the tree
 */
class WidgetOpportunityStatusSelectType extends AbstractType
{
    const NAME = 'oro_type_widget_opportunity_status_select';

    /** @var EnumOptionsProvider */
    protected $enumOptionsProvider;

    public function __construct(EnumOptionsProvider $enumOptionsProvider)
    {
        $this->enumOptionsProvider = $enumOptionsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'choices'  => $this->enumOptionsProvider->getEnumChoicesByCode('opportunity_status'),
                'multiple' => true,
                'configs'  => [
                    'allowClear' => true,
                ]
            ]
        );
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
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return Select2ChoiceType::class;
    }
}
