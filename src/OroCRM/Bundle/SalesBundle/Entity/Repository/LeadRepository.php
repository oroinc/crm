<?php

namespace OroCRM\Bundle\SalesBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use Oro\Component\DoctrineUtils\ORM\QueryUtils;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class LeadRepository extends EntityRepository
{
    /**
     * Returns count of top $limit opportunities grouped by lead source
     *
     * @param  AclHelper $aclHelper
     * @param  int       $limit
     * @param  array     $dateRange
     *
     * @return array     [itemCount, label]
     */
    public function getOpportunitiesCountByLeadSource(
        AclHelper $aclHelper,
        $limit = 10,
        array $dateRange = [],
        array $owners = []
    ) {
        $qb = $this->getOpportunitiesByLeadSourceQueryBuilder($dateRange, $owners);
        $qb->addSelect('count(o.id) as itemCount');

        $rows = $aclHelper->apply($qb)->getArrayResult();

        return $this->processOpportunitiesByLeadSource($rows, $limit);
    }

    /**
     * Returns budget ammount of top $limit opportunities grouped by lead source
     *
     * @param  AclHelper $aclHelper
     * @param  int       $limit
     * @param  array     $dateRange
     *
     * @return array     [itemCount, label]
     */
    public function getOpportunitiesAmountByLeadSource(
        AclHelper $aclHelper,
        $limit = 10,
        array $dateRange = [],
        array $owners = []
    ) {
        $qb = $this->getOpportunitiesByLeadSourceQueryBuilder($dateRange, $owners);
        $qb->addSelect(
            "SUM(
                CASE WHEN o.status = 'won' 
                    THEN o.closeRevenue 
                    ELSE o.budgetAmount
                END
            ) as itemCount"
        );

        $rows = $aclHelper->apply($qb)->getArrayResult();

        return $this->processOpportunitiesByLeadSource($rows, $limit);
    }

    /**
     * Returns opportunities QB grouped by lead source filtered by $dateRange and $ownders
     *
     * @param  array     $dateRange
     * @param  array     $owners
     *
     * @return QueryBuilder
     */
    protected function getOpportunitiesByLeadSourceQueryBuilder(array $dateRange, array $owners = [])
    {
        $qb = $this->createQueryBuilder('l')
            ->select('s.id as source')
            ->leftJoin('l.opportunities', 'o')
            ->leftJoin('l.source', 's')
            ->groupBy('source');

        if (isset($dateRange['start']) && isset($dateRange['end'])) {
            $qb->andWhere($qb->expr()->between('o.createdAt', ':dateStart', ':dateEnd'))
                ->setParameter('dateStart', $dateRange['start'])
                ->setParameter('dateEnd', $dateRange['end']);
        }

        if ($owners) {
            QueryUtils::applyOptimizedIn($qb, 'o.owner', $owners);
        }

        return $qb;
    }

    /**
     * @param array $rows
     * @param int   $limit
     *
     * @return array
     */
    protected function processOpportunitiesByLeadSource(array $rows, $limit)
    {
        $result       = [];
        $unclassified = null;
        $others       = [];

        $this->sortByCountReverse($rows);
        foreach ($rows as $row) {
            if ($row['itemCount']) {
                if ($row['source'] === null) {
                    $unclassified = $row;
                } else {
                    if (count($result) < $limit) {
                        $result[] = $row;
                    } else {
                        $others[] = $row;
                    }
                }
            }
        }

        if ($unclassified) {
            if (count($result) === $limit) {
                // allocate space for 'unclassified' item
                array_unshift($others, array_pop($result));
            }
            // add 'unclassified' item to the top to avoid moving it to $others
            array_unshift($result, $unclassified);
        }
        if (!empty($others)) {
            if (count($result) === $limit) {
                // allocate space for 'others' item
                array_unshift($others, array_pop($result));
            }
            // add 'others' item
            $result[] = [
                'source'    => '',
                'itemCount' => $this->sumCount($others)
            ];
        }

        return $result;
    }

    /**
     * @param array $rows
     *
     * @return int
     */
    protected function sumCount(array $rows)
    {
        $result = 0;
        foreach ($rows as $row) {
            $result += $row['itemCount'];
        }

        return $result;
    }

    /**
     * @param array $rows
     */
    protected function sortByCountReverse(array &$rows)
    {
        usort(
            $rows,
            function ($a, $b) {
                if ($a['itemCount'] === $b['itemCount']) {
                    return 0;
                }

                return $a['itemCount'] < $b['itemCount'] ? 1 : -1;
            }
        );
    }

    /**
     * @param AclHelper $aclHelper
     * @param \DateTime $start
     * @param \DateTime $end
     * @param int[] $owners
     *
     * @return int
     */
    public function getLeadsCount(AclHelper $aclHelper, \DateTime $start = null, \DateTime $end = null, $owners = [])
    {
        $qb = $this->createLeadsCountQb($start, $end, $owners);

        return $aclHelper->apply($qb)->getSingleScalarResult();
    }

    /**
     * @param AclHelper $aclHelper
     * @param \DateTime $start
     * @param \DateTime $end
     * @param int[] $owners
     *
     * @return int
     */
    public function getNewLeadsCount(AclHelper $aclHelper, \DateTime $start = null, \DateTime $end = null, $owners = [])
    {
        $qb = $this->createLeadsCountQb($start, $end, $owners)
            ->andWhere('l.status = :status')
            ->setParameter('status', 'new');

        return $aclHelper->apply($qb)->getSingleScalarResult();
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @param int[] $owners
     *
     * @return QueryBuilder
     */
    protected function createLeadsCountQb(\DateTime $start = null, \DateTime $end = null, $owners = [])
    {
        $qb = $this->createQueryBuilder('l');

        $qb
            ->select('COUNT(DISTINCT l.id)')
            ->innerJoin('l.opportunities', 'o');
        if ($start) {
            $qb
                ->andWhere('l.createdAt > :start')
                ->setParameter('start', $start);
        }
        if ($end) {
            $qb
                ->andWhere('l.createdAt < :end')
                ->setParameter('end', $end);
        }

        if ($owners) {
            QueryUtils::applyOptimizedIn($qb, 'l.owner', $owners);
        }

        return $qb;
    }
}
