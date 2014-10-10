<?php

namespace OroCRM\Bundle\MarketingListBundle\Provider;

use Doctrine\ORM\Query\Expr\From;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface;

use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Datagrid\ConfigurationProvider;

class MarketingListProvider
{
    const RESULT_ITEMS_MIXIN = 'orocrm-marketing-list-items-mixin';
    const RESULT_ENTITIES_MIXIN = 'orocrm-marketing-list-entities-mixin';
    const MANUAL_RESULT_ITEMS_MIXIN = 'orocrm-marketing-list-manual-items-mixin';
    const MANUAL_RESULT_ENTITIES_MIXIN = 'orocrm-marketing-list-manual-entities-mixin';

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
     * @param string|null $mixin
     *
     * @return QueryBuilder
     */
    public function getMarketingListQueryBuilder(MarketingList $marketingList, $mixin = null)
    {
        $dataGrid = $this->getMarketingListDataGrid($marketingList, $mixin);

        /** @var OrmDatasource $dataSource */
        $dataSource = $dataGrid->getAcceptedDatasource();
        return $dataSource->getQueryBuilder();
    }

    /**
     * @param MarketingList $marketingList
     *
     * @return \Iterator
     */
    public function getMarketingListResultIterator(MarketingList $marketingList)
    {
        if ($marketingList->isManual()) {
            $mixin = self::MANUAL_RESULT_ITEMS_MIXIN;
        } else {
            $mixin = self::RESULT_ITEMS_MIXIN;
        }

        $queryBuilder = $this->getMarketingListQueryBuilder($marketingList, $mixin);
        $dataGridConfig = $this->getMarketingListDataGrid($marketingList, $mixin)->getConfig();

        $skipCountWalker = $dataGridConfig->offsetGetByPath(Builder::DATASOURCE_SKIP_COUNT_WALKER_PATH, false);
        $iterator = new BufferedQueryResultIterator($queryBuilder, !$skipCountWalker);

        return $iterator;
    }

    /**
     * @param MarketingList $marketingList
     *
     * @return QueryBuilder
     */
    public function getMarketingListEntitiesQueryBuilder(MarketingList $marketingList)
    {
        if ($marketingList->isManual()) {
            $mixin = self::MANUAL_RESULT_ENTITIES_MIXIN;
        } else {
            $mixin = self::RESULT_ENTITIES_MIXIN;
        }

        $queryBuilder = clone $this->getMarketingListQueryBuilder($marketingList, $mixin);

        /** @var From[] $from */
        $from = $queryBuilder->getDQLPart('from');
        $entityAlias = $from[0]->getAlias();

        // Select only entity related information ordered by identifier field for maximum performance
        $queryBuilder
            ->resetDQLPart('select')
            ->resetDQLPart('orderBy')
            ->select($entityAlias)
            ->orderBy($entityAlias . '.id');

        return $queryBuilder;
    }

    /**
     * @param MarketingList $marketingList
     *
     * @return BufferedQueryResultIterator
     */
    public function getMarketingListEntitiesIterator(MarketingList $marketingList)
    {
        return new BufferedQueryResultIterator(
            $this->getMarketingListEntitiesQueryBuilder($marketingList),
            false
        );
    }

    /**
     * @param MarketingList $marketingList
     * @param null|string $mixin
     *
     * @return DatagridInterface
     */
    protected function getMarketingListDataGrid(MarketingList $marketingList, $mixin = null)
    {
        $dataGridName = ConfigurationProvider::GRID_PREFIX . $marketingList->getId();

        $resultKey = $dataGridName . $mixin;
        if (empty($this->dataGrid[$resultKey])) {
            $gridParameters = [
                PagerInterface::PAGER_ROOT_PARAM => [PagerInterface::DISABLED_PARAM => true]
            ];
            if ($mixin) {
                $gridParameters['grid-mixin'] = $mixin;
            }
            $this->dataGrid[$resultKey] = $this->dataGridManager->getDatagrid($dataGridName, $gridParameters);

        }

        return $this->dataGrid[$resultKey];
    }
}
