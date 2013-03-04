<?php

namespace Oro\Bundle\GridBundle\Form\Type;

use Sonata\AdminBundle\Form\Type\DateTimeRangeType as SonataDateTimeRangeType;

class DateTimeRangeType extends SonataDateTimeRangeType
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'oro_grid_type_datetime_range';
    }
}
