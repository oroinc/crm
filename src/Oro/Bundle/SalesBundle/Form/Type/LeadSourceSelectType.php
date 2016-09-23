<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Oro\Bundle\EntityExtendBundle\Provider\EnumValueProvider;

class LeadSourceSelectType extends AbstractType
{
    const NAME = 'oro_type_widget_lead_source_select';

    /** @var EnumValueProvider */
    protected $enumValueProvider;

    /**
     * @param EnumValueProvider $enumValueProvider
     */
    public function __construct(EnumValueProvider $enumValueProvider)
    {
        $this->enumValueProvider = $enumValueProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $choices = array_merge(
            [
                '' => 'oro.sales.lead.source.unclassified',
            ],
            $this->enumValueProvider->getEnumChoicesByCode('lead_source')
        );

        $resolver->setDefaults(
            [
                'choices' => $choices,
                'multiple' => true,
                'configs'  => [
                    'width'      => '400px',
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
    public function getBlockPrefix()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'genemu_jqueryselect2_choice';
    }
}
