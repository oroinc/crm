<?php

namespace Oro\Bundle\GridBundle\Form\Type\Filter;

use Sonata\AdminBundle\Form\Type\Filter\DateTimeRangeType as SonataDateTimeRangeType;

class DateTimeRangeType extends SonataDateTimeRangeType
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'oro_grid_type_filter_datetime_range';
    }
}
