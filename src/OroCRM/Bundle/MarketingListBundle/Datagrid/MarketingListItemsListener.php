<?php

namespace OroCRM\Bundle\MarketingListBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\EventListener\MixinListener;

use OroCRM\Bundle\MarketingListBundle\Model\MarketingListHelper;

class MarketingListItemsListener
{
    /**
     * @var MarketingListHelper
     */
    protected $marketingListHelper;

    /**
     * @param MarketingListHelper $marketingListHelper
     */
    public function __construct(MarketingListHelper $marketingListHelper)
    {
        $this->marketingListHelper = $marketingListHelper;
    }

    /**
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $dataGrid     = $event->getDatagrid();
        $dataGridName = $dataGrid->getName();
        $parameters   = $dataGrid->getParameters();

        if ($this->isApplicable($dataGridName, $parameters)) {
            $event->stopPropagation();
        }
    }

    /**
     * Add marketingList instance to parameters.
     *
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $dataGrid     = $event->getDatagrid();
        $dataGridName = $dataGrid->getName();
        $parameters   = $dataGrid->getParameters();
        if (!$this->isApplicable($dataGridName, $parameters)) {
            return;
        }

        $dataSource = $dataGrid->getDatasource();

        if ($dataSource instanceof OrmDatasource) {
            $marketingListId = $this->marketingListHelper->getMarketingListIdByGridName($dataGridName);
            $marketingList = $this->marketingListHelper->getMarketingList($marketingListId);

            $dataSource
                ->getQueryBuilder()
                ->addSelect($marketingList->getId() . ' as marketingList')
                ->setParameter('marketingListEntity', $marketingList);
        }
    }

    /**
     * Accept orocrm_marketing_list_items_grid_* grids only in case when they has mixin to apply.
     *
     * @param string       $gridName
     * @param ParameterBag $parameters
     *
     * @return bool
     */
    public function isApplicable($gridName, $parameters)
    {
        if (!$parameters->get(MixinListener::GRID_MIXIN, false)) {
            return false;
        }

        return (bool)$this->marketingListHelper->getMarketingListIdByGridName($gridName);
    }
}
