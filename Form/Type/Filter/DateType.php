<?php

namespace Oro\Bundle\GridBundle\Form\Type\Filter;

use Sonata\AdminBundle\Form\Type\Filter\DateType as SonataDateType;

class DateType extends SonataDateType
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'oro_grid_type_filter_date';
    }
}
