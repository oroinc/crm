<?php

namespace Oro\Bundle\GridBundle\Filter;

use Sonata\AdminBundle\Filter\FilterInterface as BaseFilterInterface;

interface FilterInterface extends BaseFilterInterface
{
    /**
     * Allowed filter types
     */
    const TYPE_BOOLEAN          = 'oro_grid_orm_boolean';
    const TYPE_CALLBACK         = 'oro_grid_orm_callback';
    const TYPE_CHOICE           = 'oro_grid_orm_choice';
    const TYPE_DATE             = 'oro_grid_orm_date';
    const TYPE_DATE_RANGE       = 'oro_grid_orm_date_range';
    const TYPE_DATETIME         = 'oro_grid_orm_datetime';
    const TYPE_DATETIME_RANGE   = 'oro_grid_orm_datetime_range';
    const TYPE_NUMBER           = 'oro_grid_orm_number';
    const TYPE_STRING           = 'oro_grid_orm_string';
    const TYPE_FLEXIBLE_STRING  = 'oro_grid_orm_flexible_string';
    const TYPE_FLEXIBLE_OPTIONS = 'oro_grid_orm_flexible_options';
}

