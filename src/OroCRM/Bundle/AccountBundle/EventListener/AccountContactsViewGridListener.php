<?php

namespace OroCRM\Bundle\AccountBundle\EventListener;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;

class AccountContactsViewGridListener
{
    /** @var RequestParameters */
    protected $requestParams;

    public function __construct(RequestParameters $requestParams)
    {
        $this->requestParams = $requestParams;
    }

    /**
     * Set current contact to query
     *
     * Event: oro_datagrid.datgrid.build.after.account-contacts-view-grid
     *
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $datasource = $event->getDatagrid()->getDatasource();
        if ($datasource instanceof OrmDatasource) {
            /** @var QueryBuilder $query */
            $queryBuilder = $datasource->getQueryBuilder();

            if ($id = $this->requestParams->get('account')) {
                $queryBuilder->setParameter('account', $id);
            }
        }
    }
} 