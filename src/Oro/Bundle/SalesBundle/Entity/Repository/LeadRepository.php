<?php

namespace Oro\Bundle\SalesBundle\Entity\Repository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Doctrine repository for Lead entity
 */
class LeadRepository extends EntityRepository
{
    /**
     * @param \DateTime|null $start
     * @param \DateTime|null $end
     *
     * @return QueryBuilder
     */
    public function getLeadsCountQB(
        ?\DateTime $start = null,
        ?\DateTime $end = null
    ) {
        return $this->createLeadsCountQb($start, $end)->innerJoin('l.opportunities', 'o');
    }

    /**
     * @param \DateTime|null $start
     * @param \DateTime|null $end
     *
     * @return QueryBuilder
     */
    public function getNewLeadsCountQB(
        ?\DateTime $start = null,
        ?\DateTime $end = null
    ) {
        return $this->createLeadsCountQb($start, $end);
    }

    /**
     * @return QueryBuilder
     */
    public function getOpenLeadsCountQB()
    {
        $qb = $this->createLeadsCountQb(null, null);
        $qb->andWhere(
            $qb->expr()->notIn(
                "JSON_EXTRACT(l.serialized_data, 'status')",
                ['lead_status.qualified', 'lead_status.canceled']
            )
        );

        return $qb;
    }

    /**
     * @param \DateTime|null $start
     * @param \DateTime|null $end
     *
     * @return QueryBuilder
     */
    protected function createLeadsCountQb(
        ?\DateTime $start = null,
        ?\DateTime $end = null
    ) {
        $qb = $this
            ->createQueryBuilder('l')
            ->select('COUNT(DISTINCT l.id)');

        if ($start) {
            $qb
                ->andWhere('l.createdAt >= :start')
                ->setParameter('start', $start, Types::DATETIME_MUTABLE);
        }
        if ($end) {
            $qb
                ->andWhere('l.createdAt <= :end')
                ->setParameter('end', $end, Types::DATETIME_MUTABLE);
        }

        return $qb;
    }
}
