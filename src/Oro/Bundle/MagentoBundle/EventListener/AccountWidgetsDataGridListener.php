<?php

namespace Oro\Bundle\MagentoBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;

class AccountWidgetsDataGridListener
{
    /**
     * @var array
     */
    protected $parameters;

    /**
     * @param array $parameters
     */
    public function __construct($parameters = array())
    {
        $this->parameters = $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $datagrid = $event->getDatagrid();
        $dataSource = $event->getDatagrid()->getDatasource();

        if ($dataSource instanceof OrmDatasource) {
            $parameters = $datagrid->getParameters();
            $queryBuilder = $dataSource->getQueryBuilder();
            $params = array();

            foreach ($this->parameters as $fieldName) {
                $param = $parameters->get($fieldName, null);
                $params[$fieldName] = $param;
            }

            $queryBuilder->setParameters($params);
        }
    }
}
