<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Sonata\DoctrineORMAdminBundle\Filter\NumberFilter as SonataNumberFilter;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class NumberFilter extends SonataNumberFilter implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        $renderSettings    = parent::getRenderSettings();
        $renderSettings[0] = 'oro_grid_type_filter_number';
        return $renderSettings;
    }
}
