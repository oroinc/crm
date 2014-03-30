<?php

namespace OroCRM\Bundle\SalesBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowStep;

class OpportunityRepository extends EntityRepository
{
    /**
     * @var WorkflowStep[]
     */
    protected $workflowStepsByName;

    /**
     * Get opportunities by state by current quarter
     *
     * @param $aclHelper AclHelper
     * @return array
     *  [
     *      'data' => [id, value]
     *      'labels' => [id, label]
     *  ]
     */
    public function getOpportunitiesByStatus(AclHelper $aclHelper)
    {
        $dateEnd = new \DateTime('now', new \DateTimeZone('UTC'));
        $dateStart = new \DateTime(
            $dateEnd->format('Y') . '-01-' . ((ceil($dateEnd->format('n') / 3) - 1) * 3 + 1),
            new \DateTimeZone('UTC')
        );
        $data = $this->getOpportunitiesDataByStatus($aclHelper, $dateStart, $dateEnd);

        $resultData = [];
        $labels = [];

        foreach ($data as $index => $dataValue) {
            $resultData[$index] = [$index, (double)$dataValue['budget']];
            $labels[$index] = $dataValue['label'];
        }

        return ['data' => $resultData, 'labels' => $labels];
    }

    /**
     * @param AclHelper $aclHelper
     * @param $dateStart
     * @param $dateEnd
     * @return array
     */
    protected function getOpportunitiesDataByStatus(AclHelper $aclHelper, $dateStart = null, $dateEnd = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $withSuffix = '';
        if ($dateStart && $dateEnd) {
            $withSuffix = ' AND ' . $qb->expr()->between('opportunity.createdAt', ':dateFrom', ':dateTo');
            $qb->setParameter('dateFrom', $dateStart)
                ->setParameter('dateTo', $dateEnd);
        }
        $qb->select('status.name', 'status.label', 'SUM(opportunity.budgetAmount) as budget')
            ->from('OroCRMSalesBundle:OpportunityStatus', 'status')
            ->leftJoin(
                'OroCRMSalesBundle:Opportunity',
                'opportunity',
                'WITH',
                'status.name = opportunity.status' . $withSuffix
            )
            ->groupBy('status.name')
            ->orderBy('status.name', 'ASC');
        $groupedData = $aclHelper->apply($qb)->getArrayResult();

        $resultData = array();
        foreach ($groupedData as $statusData) {
            if (!$statusData['budget']) {
                $statusData['budget'] = 0;
            }
            $resultData[] = $statusData;
        }

        return $resultData;
    }
}
