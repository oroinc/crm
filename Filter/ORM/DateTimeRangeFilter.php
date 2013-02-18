<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Sonata\DoctrineORMAdminBundle\Filter\DateTimeRangeFilter as SonataDateTimeRangeFilter;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class DateTimeRangeFilter extends SonataDateTimeRangeFilter implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        $renderSettings    = parent::getRenderSettings();
        $renderSettings[0] = 'oro_grid_type_filter_datetime_range';
        return $renderSettings;
    }
}
