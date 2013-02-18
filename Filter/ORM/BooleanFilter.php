<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Sonata\DoctrineORMAdminBundle\Filter\BooleanFilter as SonataBooleanFilter;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class BooleanFilter extends SonataBooleanFilter implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        $renderSettings    = parent::getRenderSettings();
        $renderSettings[0] = 'oro_grid_type_filter_default';
        return $renderSettings;
    }
}
