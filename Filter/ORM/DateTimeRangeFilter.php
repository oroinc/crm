<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Symfony\Component\Translation\TranslatorInterface;
use Sonata\DoctrineORMAdminBundle\Filter\DateTimeRangeFilter as SonataDateTimeRangeFilter;
use Sonata\AdminBundle\Form\Type\Filter\DateTimeRangeType;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class DateTimeRangeFilter extends SonataDateTimeRangeFilter implements FilterInterface
{
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
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        $renderSettings    = parent::getRenderSettings();
        $renderSettings[0] = 'oro_grid_type_filter_datetime_range';
        return $renderSettings;
    }

    /**
     * @return array
     */
    public function getTypeOptions()
    {
        return array(
            DateTimeRangeType::TYPE_BETWEEN
                => $this->translator->trans('label_date_type_between', array(), 'SonataAdminBundle'),
            DateTimeRangeType::TYPE_NOT_BETWEEN
                => $this->translator->trans('label_date_type_not_between', array(), 'SonataAdminBundle'),
        );
    }
}
