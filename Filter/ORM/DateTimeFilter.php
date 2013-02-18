<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Sonata\DoctrineORMAdminBundle\Filter\DateTimeFilter as SonataDateTimeFilter;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class DateTimeFilter extends SonataDateTimeFilter implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        $renderSettings    = parent::getRenderSettings();
        $renderSettings[0] = 'oro_grid_type_filter_datetime';
        return $renderSettings;
    }
}
