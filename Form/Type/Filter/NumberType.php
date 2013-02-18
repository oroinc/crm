<?php

namespace Oro\Bundle\GridBundle\Form\Type\Filter;

use Sonata\AdminBundle\Form\Type\Filter\NumberType as SonataNumberType;

class NumberType extends SonataNumberType
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'oro_grid_type_filter_number';
    }
}
