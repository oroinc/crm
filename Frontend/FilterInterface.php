<?php

namespace Oro\Bundle\FilterBundle\Frontend;

interface FilterInterface
{
    /**
     * Predefined filter types
     */
    const TYPE_DATE              = 'oro_filter_date';
    const TYPE_DATETIME          = 'oro_filter_datetime';
    const TYPE_NUMBER            = 'oro_filter_number';
    const TYPE_STRING            = 'oro_filter_string';
    const TYPE_SELECT            = 'oro_filter_select';
    const TYPE_MULTISELECT       = 'oro_filter_multiselect';

    /**
     * Get type
     *
     * @return string
     */
    public function getType();

    /**
     * Get name
     *
     * @return string
     */
    public function getName();

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions();
}
