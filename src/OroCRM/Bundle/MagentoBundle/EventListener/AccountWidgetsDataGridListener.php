<?php

namespace OroCRM\Bundle\MagentoBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;

class AccountWidgetsDataGridListener
{
    /**
     * @var array
     */
    protected $parameters;
    /**
     * @var RequestParameters
     */
    protected $requestParams;

    /**
     * @param RequestParameters $requestParams
     * @param array $parameters
     */
    public function __construct(RequestParameters $requestParams, $parameters = array())
    {
        $this->parameters = $parameters;
        $this->requestParams = $requestParams;
    }

    /**
     * @inheritdoc
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $dataSource = $event->getDatagrid()->getDatasource();
        if ($dataSource instanceof OrmDatasource) {

            $queryBuilder = $dataSource->getQueryBuilder();
            $params = array();

            foreach ($this->parameters as $fieldName) {
                $param = $this->requestParams->get($fieldName, null);
                $params[$fieldName] = $param;
            }

            $queryBuilder->setParameters($params);
        }
    }
}
