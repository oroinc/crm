<?php

namespace OroCRM\Bundle\MarketingListBundle\Datagrid\Extension;

use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\From;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use OroCRM\Bundle\MarketingListBundle\Model\MarketingListHelper;

class MarketingListExtension extends AbstractExtension
{
    const OPTIONS_MIXIN_PATH = '[options][mixin]';

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
     * {@inheritDoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        if ($config->offsetGetByPath(Builder::DATASOURCE_TYPE_PATH) !== OrmDatasource::TYPE) {
            return false;
        }

        if (!$config->offsetGetByPath(self::OPTIONS_MIXIN_PATH, false)) {
            return false;
        }

        return (bool)$this->marketingListHelper
            ->getMarketingListIdByGridName($config->offsetGetByPath('[name]'));
    }

    /**
     * {@inheritdoc}
     */
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
        if (!$this->isApplicable($config)) {
            return;
        }

        /** @var OrmDatasource $datasource */
        $qb       = $datasource->getQueryBuilder();
        $dqlParts = $qb->getDQLParts();

        /** @var Andx $conditions */
        $conditions = $dqlParts['where'];
        if (empty($conditions)) {
            return;
        }

        $parts = $conditions->getParts();
        if (empty($parts)) {
            return;
        }

        $qb->resetDQLPart('where');

        $addParameter = false;
        foreach ($parts as $part) {
            if (!is_string($part)) {
                $part = $qb->expr()->orX(
                    $part,
                    $this->createItemsFunc($qb)
                );

                $addParameter = true;
            }

            $qb->andWhere($part);
        }

        if ($addParameter) {
            $qb->setParameter(
                'marketingListId',
                $this->marketingListHelper->getMarketingListIdByGridName(
                    $config->offsetGetByPath('[name]')
                )
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getPriority()
    {
        return -10;
    }

    /**
     * @param QueryBuilder $qb
     *
     * @return Func
     */
    protected function createItemsFunc(QueryBuilder $qb)
    {
        $itemsQb = clone $qb;
        $itemsQb->resetDQLParts();

        $itemsQb
            ->select('mli.entityId')
            ->from('OroCRMMarketingListBundle:MarketingListItem', 'item')
            ->where('item.marketingList = :marketingListId');

        /** @var From[] $from */
        $from = $qb->getDQLPart('from');
        $alias = $from ? $from[0]->getAlias() : 't1';

        return new Func($alias . '.id IN', [$itemsQb->getDQL()]);
    }
}
