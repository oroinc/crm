<?php

namespace Oro\Bundle\GridBundle\Form\Type;

use Sonata\AdminBundle\Form\Type\DateRangeType as SonataDateRangeType;
use Symfony\Component\Form\FormBuilderInterface;

class DateRangeType extends SonataDateRangeType
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'oro_grid_type_date_range';
    }
}
