<?php

namespace OroCRM\Bundle\SalesBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowStep;
use OroCRM\Bundle\SalesBundle\Entity\OpportunityStatus;

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
        $resultData = array();

        /** @var OpportunityStatus[] $statuses */
        $statuses = $this->getEntityManager()->getRepository('OroCRMSalesBundle:OpportunityStatus')->findAll();
        foreach ($statuses as $status) {
            $resultData[$status->getName()] = array(
                'label' => $status->getLabel(),
                'budget' => 0,
            );
        }

        $qb = $this->createQueryBuilder('opp');
        $qb->select('opp_status.name', 'SUM(opp.budgetAmount) as budget')
            ->join('opp.status', 'opp_status');
        if ($dateStart && $dateEnd) {
            $qb->where($qb->expr()->between('opp.createdAt', ':dateFrom', ':dateTo'))
                ->setParameter('dateFrom', $dateStart)
                ->setParameter('dateTo', $dateEnd);
        }
        $qb->groupBy('opp_status.name');
        $groupedData = $aclHelper->apply($qb)->getArrayResult();

        foreach ($groupedData as $statusData) {
            $statusName = $statusData['name'];
            $resultData[$statusName]['budget'] = $statusData['budget'];
        }

        return array_values($resultData);
    }
}
