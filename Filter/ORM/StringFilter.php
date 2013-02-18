<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Sonata\DoctrineORMAdminBundle\Filter\StringFilter as SonataStringFilter;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class StringFilter extends SonataStringFilter implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        $renderSettings    = parent::getRenderSettings();
        $renderSettings[0] = 'oro_grid_type_filter_choice';
        return $renderSettings;
    }
}
