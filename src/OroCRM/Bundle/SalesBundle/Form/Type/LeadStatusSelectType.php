<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\EntityExtendBundle\Provider\EnumValueProvider;

use OroCRM\Bundle\SalesBundle\Entity\Lead;

class LeadStatusSelectType extends AbstractType
{
    const NAME = 'orocrm_type_widget_lead_status_select';

    /** @var EnumValueProvider */
    protected $enumValueProvider;

    /**
     * @param EnumValueProvider $enumValueProvider
     */
    public function __construct(EnumValueProvider $enumValueProvider)
    {
        $this->enumValueProvider = $enumValueProvider;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(
            [
                'choices'  => $this->enumValueProvider->getEnumChoicesByCode(Lead::INTERNAL_STATUS_CODE),
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
