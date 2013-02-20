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
     * @var string
     */
    protected $flexibleManagerServiceId;

    /**
     * @param FlexibleManager $flexibleManager
     * @param string $serviceId
     */
    public function setFlexibleManager(FlexibleManager $flexibleManager, $serviceId)
    {
        $this->flexibleManager          = $flexibleManager;
        $this->flexibleManagerServiceId = $serviceId;

        // TODO: somehow get from parameters interface
        $this->flexibleManager->setLocale('en');
        $this->flexibleManager->setScope('ecommerce');
    }
}
