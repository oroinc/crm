<?php

namespace OroCRM\Bundle\MagentoBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\SecurityBundle\SecurityFacade;

/**
 * Listener for grid "magento-customers-group-by-channel-grid".
 *
 * @see \OroCRM\Bundle\MagentoBundle\Autocomplete\CustomerGroupSearchHandler
 */
class CustomerGroupGridListener
{
    /** @var SecurityFacade */
    protected $securityFacade;

    /**
     * @param SecurityFacade $securityFacade
     */
    public function __construct(SecurityFacade $securityFacade)
    {
        $this->securityFacade = $securityFacade;
    }

    /**
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $datagrid   = $event->getDatagrid();
        $datasource = $datagrid->getDatasource();
        if ($datasource instanceof OrmDatasource) {
            $queryBuilder = $datasource->getQueryBuilder();
            $isGranted = $this->securityFacade->isGranted('oro_integration_assign');
            // if permission for integration channel assign is not granted
            if (!$isGranted) {
                $queryBuilder->andWhere('1 = 0');
            }
        }
    }
}
