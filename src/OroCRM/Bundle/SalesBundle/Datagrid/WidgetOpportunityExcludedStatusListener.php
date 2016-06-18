<?php

namespace OroCRM\Bundle\SalesBundle\Datagrid;

use Doctrine\ORM\Query;

use Oro\Bundle\DataGridBundle\Event\OrmResultBefore;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;

use Oro\Bundle\DashboardBundle\Model\WidgetConfigs;

class WidgetOpportunityExcludedStatusListener
{
    /** @var WidgetConfigs */
    protected $widgetConfigs;

    /**
     * @param WidgetConfigs $widgetConfigs
     */
    public function __construct(WidgetConfigs $widgetConfigs)
    {
        $this->widgetConfigs = $widgetConfigs;
    }

    /**
     * @param OrmResultBefore $event
     */
    public function onResultBefore(OrmResultBefore $event)
    {
        $widgetOptions = $this->widgetConfigs->getWidgetOptions();
        $statuses      = $widgetOptions->get('excluded_statuses', []);

        if ($statuses) {
            /** @var OrmDatasource $dataSource */
            $dataSource  = $event->getDatagrid()->getDatasource();
            $qb          = $dataSource->getQueryBuilder();
            $rootAliases = $qb->getRootAliases();
            $field       = sprintf('%s.status', reset($rootAliases));
            $qb->andWhere($qb->expr()->notIn($field, ':statuses'));

            /** @var Query $query */
            $query = $event->getQuery();
            $query->setDQL($dataSource->getQueryBuilder()->getQuery()->getDQL());
            $query->setParameter('statuses', $statuses);
        }
    }
}
