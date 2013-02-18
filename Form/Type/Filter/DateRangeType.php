<?php

namespace Oro\Bundle\GridBundle\Form\Type\Filter;

use Sonata\AdminBundle\Form\Type\Filter\DateRangeType as SonataDateRangeType;

class DateRangeType extends SonataDateRangeType
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'oro_grid_type_filter_date_range';
    }
}
