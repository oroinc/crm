<?php

namespace Oro\Bundle\FilterBundle\Form\Type\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\FilterBundle\Form\Type\DateTimeRangeType as BasicDateTimeRangeType;

class DateTimeRangeType extends AbstractType
{
    const TYPE_BETWEEN = DateRangeType::TYPE_BETWEEN;
    const TYPE_NOT_BETWEEN = DateRangeType::TYPE_NOT_BETWEEN;
    const NAME = 'oro_type_datetime_range_filter';

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
        return DateRangeType::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'field_type' => BasicDateTimeRangeType::NAME
            )
        );
    }
}
