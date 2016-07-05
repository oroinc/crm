<?php

namespace OroCRM\Bundle\SalesBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use Oro\Component\DoctrineUtils\ORM\QueryUtils;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class LeadRepository extends EntityRepository
{
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
