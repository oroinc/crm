<?php

namespace Oro\Bundle\MagentoBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Listener for grid "magento-customers-group-by-channel-grid".
 *
 * @see \Oro\Bundle\MagentoBundle\Autocomplete\CustomerGroupSearchHandler
 */
class CustomerGroupGridListener
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
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
            $isGranted = $this->authorizationChecker->isGranted('oro_integration_assign');
            // if permission for integration channel assign is not granted
            if (!$isGranted) {
                $queryBuilder->andWhere('1 = 0');
            }
        }
    }
}
