<?php

namespace OroCRM\Bundle\MarketingListBundle\Datagrid\Extension;

use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use OroCRM\Bundle\MarketingListBundle\Model\MarketingListSegmentHelper;

class MarketingListExtension extends AbstractExtension
{
    /**
     * @var MarketingListSegmentHelper
     */
    protected $segmentHelper;

    /**
     * @param MarketingListSegmentHelper $segmentHelper
     */
    public function __construct(MarketingListSegmentHelper $segmentHelper)
    {
        $this->segmentHelper = $segmentHelper;
    }

    /**
     * {@inheritDoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        if ($config->offsetGetByPath(Builder::DATASOURCE_TYPE_PATH) !== OrmDatasource::TYPE) {
            return false;
        }

        $segmentId = $this->segmentHelper->getSegmentIdByGridName($config->offsetGetByPath('[name]'));
        if (!$segmentId) {
            return false;
        }

        return (bool)$this->segmentHelper->getMarketingListBySegment($segmentId);
    }

    /**
     * {@inheritdoc}
     */
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
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
                'segmentId',
                $this->segmentHelper->getSegmentIdByGridName(
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
            ->leftJoin('item.marketingList', 'list')
            ->where('list.segment = :segmentId');

        return new Func('t1.id IN', [$itemsQb->getDQL()]);
    }
}
