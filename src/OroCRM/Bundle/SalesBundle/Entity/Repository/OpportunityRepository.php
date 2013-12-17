<?php

namespace OroCRM\Bundle\SalesBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class OpportunityRepository extends EntityRepository
{
    /**
     * Get opportunities by state
     *
     * @param $aclHelper AclHelper
     * @return array
     *  [
     *      'data' => [id, value]
     *      'labels' => [id, label]
     *  ]
     */
    public function getOpportunitiesByState($aclHelper)
    {
        $dateEnd = new \DateTime('now', new \DateTimeZone('UTC'));
        $dateStart = clone $dateEnd;
        $dateStart = $dateStart->sub(new \DateInterval('P1M'));
        $qb = $this->createQueryBuilder('opp');
        $qb->select('opp_status.label', 'SUM(opp.budgetAmount) as budget')
             ->join('opp.status', 'opp_status')
             ->where($qb->expr()->between('opp.createdAt', ':dateFrom', ':dateTo'))
             ->setParameter('dateFrom', $dateStart)
             ->setParameter('dateTo', $dateEnd)
             ->groupBy('opp_status.name');

        $data = $aclHelper->apply($qb)
             ->getArrayResult();

        $resultData = [];
        $labels = [];

        foreach ($data as $index => $dataValue) {
            $resultData[$index] = [$index, (double)$dataValue['budget']];
            $labels[$index] = $dataValue['label'];
        }

        return ['data' => $resultData, 'labels' => $labels];
    }
}
