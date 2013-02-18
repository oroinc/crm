<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter as SonataCallbackFilter;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class CallbackFilter extends SonataCallbackFilter implements FilterInterface
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
