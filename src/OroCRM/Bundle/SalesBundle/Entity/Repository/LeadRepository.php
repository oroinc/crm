<?php

namespace OroCRM\Bundle\SalesBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use Oro\Component\DoctrineUtils\ORM\QueryUtils;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class LeadRepository extends EntityRepository
{
    /**
     * Returns count of opportunities grouped by lead source
     *
     * @param AclHelper $aclHelper
     * @param array $dateRange
     * @param array $owners
     *
     * @return array [value, source]
     */
    public function getOpportunitiesCountGroupByLeadSource(
        AclHelper $aclHelper,
        array $dateRange = [],
        array $owners = []
    ) {
        $qb = $this->getOpportunitiesGroupByLeadSourceQueryBuilder($dateRange, $owners);
        $qb->addSelect('count(o.id) as value');

        return $aclHelper->apply($qb)->getArrayResult();
    }

    /**
     * Returns budget amount of opportunities grouped by lead source
     *
     * @param AclHelper $aclHelper
     * @param array $dateRange
     * @param array $owners
     *
     * @return array [value, source]
     */
    public function getOpportunitiesAmountGroupByLeadSource(
        AclHelper $aclHelper,
        array $dateRange = [],
        array $owners = []
    ) {
        $qb = $this->getOpportunitiesGroupByLeadSourceQueryBuilder($dateRange, $owners);
        $qb->addSelect(
            "SUM(
                CASE WHEN o.status = 'won' 
                    THEN o.closeRevenue 
                    ELSE o.budgetAmount
                END
            ) as value"
        );

        return $aclHelper->apply($qb)->getArrayResult();
    }

    /**
     * Returns opportunities QB grouped by lead source filtered by $dateRange and $ownders
     *
     * @param  array     $dateRange
     * @param  array     $owners
     *
     * @return QueryBuilder
     */
    protected function getOpportunitiesGroupByLeadSourceQueryBuilder(array $dateRange, array $owners = [])
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
