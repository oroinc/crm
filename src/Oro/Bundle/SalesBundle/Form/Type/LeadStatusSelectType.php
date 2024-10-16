<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Oro\Bundle\EntityExtendBundle\Provider\EnumOptionsProvider;
use Oro\Bundle\FormBundle\Form\Type\Select2ChoiceType;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type provides functionality to select an existing entity from the tree
 */
class LeadStatusSelectType extends AbstractType
{
    const string NAME = 'oro_type_widget_lead_status_select';

    public function __construct(protected EnumOptionsProvider $enumOptionsProvider)
    {
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(
            [
                'choices' => $this->enumOptionsProvider->getEnumChoicesByCode(Lead::INTERNAL_STATUS_CODE),
                'multiple' => true,
                'configs' => [
                    'allowClear' => true,
                ]
            ]
        );
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }

    #[\Override]
    public function getParent(): ?string
    {
        return Select2ChoiceType::class;
    }
}
