<?php

namespace Oro\Bundle\GridBundle\Form\Type\Filter;

use Sonata\AdminBundle\Form\Type\Filter\DefaultType as SonataDefaultType;

class DefaultType extends SonataDefaultType
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'oro_grid_type_filter_default';
    }
}
