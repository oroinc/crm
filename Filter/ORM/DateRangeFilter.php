<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter as SonataDateRangeFilter;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class DateRangeFilter extends SonataDateRangeFilter implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        $renderSettings    = parent::getRenderSettings();
        $renderSettings[0] = 'oro_grid_type_filter_date_range';
        return $renderSettings;
    }
}
