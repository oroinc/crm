<?php

namespace OroCRM\Bundle\ContactBundle\EventListener;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;

class ContactGroupContactsListener
{
    const GRID_PARAM_DATA_IN     = 'data_in';
    const GRID_PARAM_DATA_NOT_IN = 'data_not_in';
    const GRID_GROUP_PARAM       = 'group';

    /**
     * @param RequestParameters $requestParams
     */
    public function __construct(RequestParameters $requestParams)
    {
        $this->requestParams = $requestParams;
    }

    /**
     * Set group to and checkboxes to contact group datagrid
     * Event:
     *
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $datasource = $event->getDatagrid()->getDatasource();
        if ($datasource instanceof OrmDatasource) {
            /** @var QueryBuilder $query */
            $queryBuilder = $datasource->getQuery();

            $queryParameters = array(
                self::GRID_GROUP_PARAM => $this->requestParams->get(self::GRID_GROUP_PARAM, null),
                'data_in'              => $this->requestParams->get(self::GRID_PARAM_DATA_IN, [0]),
                'data_not_in'          => $this->requestParams->get(self::GRID_PARAM_DATA_NOT_IN, [0]),
            );
            $queryBuilder->setParameters($queryParameters);
        }
    }
}
