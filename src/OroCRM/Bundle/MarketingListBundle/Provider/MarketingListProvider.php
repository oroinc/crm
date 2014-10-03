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
    const RESULT_ITEMS_MIXIN    = 'orocrm-marketing-list-items-mixin';
    const RESULT_ENTITIES_MIXIN = 'orocrm-marketing-list-entities-mixin';

    /**
     * @var Manager
     */
    protected $dataGridManager;

    /**
     * @var array
     */
    protected $dataGrid = [];

    /**
     * @param Manager $dataGridManager
     */
    public function __construct(Manager $dataGridManager)
    {
        $this->dataGridManager = $dataGridManager;
    }

    /**
     * @param MarketingList $marketingList
     * @param string|null   $mixin
     *
     * @return QueryBuilder|null
     */
    public function getMarketingListQueryBuilder(MarketingList $marketingList, $mixin = null)
    {
        if ($marketingList->getType()->getName() !== MarketingListType::TYPE_MANUAL) {
            $dataGrid = $this->getSegmentDataGrid($marketingList->getSegment(), $mixin);

            /** @var OrmDatasource $dataSource */
            $dataSource   = $dataGrid->getAcceptedDatasource();
            $queryBuilder = $dataSource->getQueryBuilder();

            return $queryBuilder;
        }

        return null;
    }

    /**
     * @param MarketingList $marketingList
     *
     * @return null|\Iterator
     */
    public function getMarketingListResultIterator(MarketingList $marketingList)
    {
        if ($marketingList->getType()->getName() !== MarketingListType::TYPE_MANUAL) {
            $queryBuilder = $this->getMarketingListQueryBuilder(
                $marketingList,
                self::RESULT_ITEMS_MIXIN
            );
            $dataGridConfig = $this
                ->getSegmentDataGrid(
                    $marketingList->getSegment(),
                    self::RESULT_ITEMS_MIXIN
                )
                ->getConfig();

            $skipCountWalker = $dataGridConfig->offsetGetByPath(Builder::DATASOURCE_SKIP_COUNT_WALKER_PATH, false);
            $iterator        = new BufferedQueryResultIterator($queryBuilder, !$skipCountWalker);

            return $iterator;
        }

        return null;
    }

    /**
     * @param MarketingList $marketingList
     *
     * @return QueryBuilder|null
     */
    public function getMarketingListEntitiesQueryBuilder(MarketingList $marketingList)
    {
        if ($marketingList->getType()->getName() !== MarketingListType::TYPE_MANUAL) {
            $queryBuilder = clone $this->getMarketingListQueryBuilder(
                $marketingList,
                self::RESULT_ENTITIES_MIXIN
            );
            // Select only entity related information ordered by identifier field for maximum performance
            $queryBuilder
                ->resetDQLPart('select')
                ->resetDQLPart('orderBy')
                ->select('t1')
                ->orderBy('t1.id');

            return $queryBuilder;
        }

        return null;
    }

    /**
     * @param MarketingList $marketingList
     *
     * @return BufferedQueryResultIterator|null
     */
    public function getMarketingListEntitiesIterator(MarketingList $marketingList)
    {
        if ($marketingList->getType()->getName() !== MarketingListType::TYPE_MANUAL) {
            return new BufferedQueryResultIterator($this->getMarketingListEntitiesQueryBuilder($marketingList), false);
        }

        return null;
    }

    /**
     * @param Segment     $segment
     * @param null|string $mixin
     *
     * @return DatagridInterface
     */
    protected function getSegmentDataGrid(Segment $segment, $mixin = null)
    {
        $dataGridName = $segment->getGridPrefix() . $segment->getId();

        $resultKey = $dataGridName . $mixin;
        if (empty($this->dataGrid[$resultKey])) {
            $gridParameters = [
                PagerInterface::PAGER_ROOT_PARAM => array(PagerInterface::DISABLED_PARAM => true)
            ];
            if ($mixin) {
                $gridParameters['grid-mixin'] = $mixin;
            }
            $this->dataGrid[$resultKey] = $this->dataGridManager->getDatagrid($dataGridName, $gridParameters);

        }

        return $this->dataGrid[$resultKey];
    }
}
