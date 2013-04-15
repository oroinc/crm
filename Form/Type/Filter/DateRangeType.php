<?php

namespace Oro\Bundle\FilterBundle\Form\Type\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\FilterBundle\Form\Type\DateRangeType as BasicDateRangeType;

class DateRangeType extends AbstractType
{
    const TYPE_BETWEEN = 1;
    const TYPE_NOT_BETWEEN = 2;
    const NAME = 'oro_type_date_range_filter';

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return ChoiceType::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $choices = array(
            self::TYPE_BETWEEN
                => $this->translator->trans('label_date_type_between', array(), 'OroFilterBundle'),
            self::TYPE_NOT_BETWEEN
                => $this->translator->trans('label_date_type_not_between', array(), 'OroFilterBundle'),
        );

        $resolver->setDefaults(
            array(
                'field_type' => BasicDateRangeType::NAME,
                'choices' => $choices
            )
        );
    }
}
