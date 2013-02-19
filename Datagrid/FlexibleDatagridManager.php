<?php

namespace Oro\Bundle\GridBundle\Datagrid;

use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

abstract class FlexibleDatagridManager extends DatagridManager
{
    /**
     * @var FlexibleManager
     */
    protected $flexibleManager;

    /**
     * @param FlexibleManager $flexibleManager
     */
    public function setFlexibleManager(FlexibleManager $flexibleManager)
    {
        $this->flexibleManager = $flexibleManager;

        // TODO: somehow get from parameters interface
        $this->flexibleManager->setLocale('en');
        $this->flexibleManager->setScope('ecommerce');
    }
}
