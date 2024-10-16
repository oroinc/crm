<?php

namespace Oro\Bundle\SalesBundle\Datagrid;

use Doctrine\ORM\Query;
use Oro\Bundle\DashboardBundle\Model\WidgetConfigs;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\OrmResultBefore;

/**
 * Excluding entities by status.
 */
class WidgetExcludedStatusListener
{
    /** @var WidgetConfigs */
    protected $widgetConfigs;

    public function __construct(WidgetConfigs $widgetConfigs)
    {
        $this->widgetConfigs = $widgetConfigs;
    }

    public function onResultBefore(OrmResultBefore $event)
    {
        $widgetOptions = $this->widgetConfigs->getWidgetOptions();
        $statuses      = $widgetOptions->get('excluded_statuses', []);

        if ($statuses) {
            /** @var OrmDatasource $dataSource */
            $dataSource  = $event->getDatagrid()->getDatasource();
            $qb          = $dataSource->getQueryBuilder();
            $rootAliases = $qb->getRootAliases();
            $qb->andWhere(
                $qb->expr()->notIn(
                    "JSON_EXTRACT(" . reset($rootAliases) . ".serialized_data, 'status')",
                    ':statuses'
                )
            );
            /** @var Query $query */
            $query = $event->getQuery();
            $query->setDQL($dataSource->getQueryBuilder()->getQuery()->getDQL());
            $query->setParameter('statuses', $statuses);
        }
    }
}
