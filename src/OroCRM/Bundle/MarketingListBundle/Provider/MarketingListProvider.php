<?php

namespace OroCRM\Bundle\MarketingListBundle\Provider;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface;
use Oro\Bundle\SegmentBundle\Entity\Segment;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingListType;

class MarketingListProvider
{
    /**
     * @var Manager
     */
    protected $dataGridManager;

    /**
     * @var array
     */
    protected $dataGrid = array();

    /**
     * @param Manager $dataGridManager
     */
    public function __construct(Manager $dataGridManager)
    {
        $this->dataGridManager = $dataGridManager;
    }

    /**
     * @param MarketingList $marketingList
     * @return QueryBuilder|null
     */
    public function getMarketingListQueryBuilder(MarketingList $marketingList)
    {
        if ($marketingList->getType()->getName() !== MarketingListType::TYPE_MANUAL) {
            $dataGrid = $this->getSegmentDataGrid($marketingList->getSegment());

            /** @var OrmDatasource $dataSource */
            $dataSource = $dataGrid->getDatasource();
            $queryBuilder = $dataSource->getQueryBuilder();

            return $queryBuilder;
        }

        return null;
    }

    /**
     * @param MarketingList $marketingList
     * @return null|\Iterator
     */
    public function getMarketingListResultIterator(MarketingList $marketingList)
    {
        if ($marketingList->getType()->getName() !== MarketingListType::TYPE_MANUAL) {
            $queryBuilder = $this->getMarketingListQueryBuilder($marketingList);
            $dataGridConfig = $this->getSegmentDataGrid($marketingList->getSegment())->getConfig();
            $skipCountWalker = $dataGridConfig->offsetGetByPath(Builder::DATASOURCE_SKIP_COUNT_WALKER_PATH);
            $useWalker = null;
            if ($skipCountWalker !== null) {
                $useWalker = !$skipCountWalker;
            }
            $iterator = new BufferedQueryResultIterator($queryBuilder, $useWalker);

            return $iterator;
        }

        return null;
    }

    /**
     * @param Segment $segment
     * @return DatagridInterface
     */
    protected function getSegmentDataGrid(Segment $segment)
    {
        $dataGridName = $segment->getGridPrefix() . $segment->getId();

        if (empty($this->dataGrid[$dataGridName])) {
            $this->dataGrid[$dataGridName] = $this->dataGridManager->getDatagrid(
                $dataGridName,
                array(PagerInterface::PAGER_ROOT_PARAM => array(PagerInterface::DISABLED_PARAM => true))
            );
        }

        return $this->dataGrid[$dataGridName];
    }
}
