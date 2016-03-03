<?php

namespace OroCRM\Bundle\MarketingListBundle\Provider;

use Doctrine\ORM\Query\Expr\From;
use Doctrine\ORM\Query\Expr\Select;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface;
use Oro\Bundle\TagBundle\Grid\TagsExtension;

use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Datagrid\ConfigurationProvider;

class MarketingListProvider
{
    const RESULT_ITEMS_MIXIN = 'orocrm-marketing-list-items-mixin';
    const RESULT_ENTITIES_MIXIN = 'orocrm-marketing-list-entities-mixin';
    const FULL_ENTITIES_MIXIN = 'orocrm-marketing-full-mixin';
    const MANUAL_RESULT_ITEMS_MIXIN = 'orocrm-marketing-list-manual-items-mixin';
    const MANUAL_RESULT_ENTITIES_MIXIN = 'orocrm-marketing-list-manual-entities-mixin';
    const DATAGRID_COLUMN_ALIASES_PATH = '[source][query_config][column_aliases]';

    /**
     * @var Manager
     */
    protected $dataGridManager;

    /**
     * @var array
     */
    protected $dataGrid = [];

    /**
     * @var array
     */
    protected $columnInformation = [];

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
        $qb =  $dataSource->getQueryBuilder();
        $this->saveColumnInformation($marketingList, $dataGrid, $qb);

        return $qb;
    }

    /**
     * @param MarketingList $marketingList
     * @param string|null $mixin
     *
     * @return \Iterator
     */
    public function getMarketingListResultIterator(MarketingList $marketingList, $mixin = null)
    {
        if (!$mixin) {
            if ($marketingList->isManual()) {
                $mixin = self::MANUAL_RESULT_ITEMS_MIXIN;
            } else {
                $mixin = self::RESULT_ITEMS_MIXIN;
            }
        }

        $queryBuilder = $this->getMarketingListQueryBuilder($marketingList, $mixin);
        $dataGridConfig = $this->getMarketingListDataGrid($marketingList, $mixin)->getConfig();

        $skipCountWalker = $dataGridConfig->offsetGetByPath(
            DatagridConfiguration::DATASOURCE_SKIP_COUNT_WALKER_PATH,
            false
        );
        $iterator = new BufferedQueryResultIterator($queryBuilder, !$skipCountWalker);

        return $iterator;
    }

    /**
     * @param MarketingList $marketingList
     * @param string|null $mixin
     *
     * @return QueryBuilder
     */
    public function getMarketingListEntitiesQueryBuilder(MarketingList $marketingList, $mixin = null)
    {
        if (!$mixin) {
            if ($marketingList->isManual()) {
                $mixin = self::MANUAL_RESULT_ENTITIES_MIXIN;
            } else {
                $mixin = self::RESULT_ENTITIES_MIXIN;
            }
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
     * @param string $mixin
     *
     * @return BufferedQueryResultIterator
     */
    public function getMarketingListEntitiesIterator(MarketingList $marketingList, $mixin = null)
    {
        return new BufferedQueryResultIterator(
            $this->getMarketingListEntitiesQueryBuilder($marketingList, $mixin),
            false
        );
    }

    /**
     * @param MarketingList $marketingList
     * @return null|array
     */
    public function getColumnInformation(MarketingList $marketingList)
    {
        if (array_key_exists($marketingList->getId(), $this->columnInformation)) {
            return $this->columnInformation[$marketingList->getId()];
        }

        return null;
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
                PagerInterface::PAGER_ROOT_PARAM => [PagerInterface::DISABLED_PARAM => true],
                /**
                 * Disable tags extension because of problems with SecurityFacade::isGranted
                 * calls during console command running
                 */
                TagsExtension::TAGS_ROOT_PARAM => [TagsExtension::DISABLED_PARAM => true]
            ];
            if ($mixin) {
                $gridParameters['grid-mixin'] = $mixin;
            }
            $this->dataGrid[$resultKey] = $this->dataGridManager->getDatagrid($dataGridName, $gridParameters);

        }

        return $this->dataGrid[$resultKey];
    }

    /**
     * @param MarketingList $marketingList
     * @param DatagridInterface $dataGrid
     * @param QueryBuilder $qb
     */
    protected function saveColumnInformation(
        MarketingList $marketingList,
        DatagridInterface $dataGrid,
        QueryBuilder $qb
    ) {
        /** @var Select[] $selects */
        $selects = $qb->getDQLPart('select');
        $columnToSelectExpr = [];
        foreach ($selects as $select) {
            foreach ($select->getParts() as $selectPart) {
                $selectData = explode(strrev(' as '), strrev($selectPart), 2);
                if (count($selectData) === 2) {
                    $columnToSelectExpr[strrev($selectData[0])] = strrev($selectData[1]);
                }
            }
        }

        $columnAliases = $dataGrid->getConfig()->offsetGetByPath(self::DATAGRID_COLUMN_ALIASES_PATH);
        $columnInformation = [];
        foreach ($columnAliases as $alias => $selectAlias) {
            if (array_key_exists($selectAlias, $columnToSelectExpr)) {
                $columnInformation[$alias] = $columnToSelectExpr[$selectAlias];
            }
        }

        $this->columnInformation[$marketingList->getId()] = $columnInformation;
    }
}
