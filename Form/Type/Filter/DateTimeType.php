<?php

namespace Oro\Bundle\GridBundle\Form\Type\Filter;

use Sonata\AdminBundle\Form\Type\Filter\DateTimeType as SonataDateTimeType;

class DateTimeType extends SonataDateTimeType
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'oro_grid_type_filter_datetime';
    }
}
