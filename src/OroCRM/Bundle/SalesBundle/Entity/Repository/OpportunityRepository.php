<?php

namespace OroCRM\Bundle\SalesBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class OpportunityRepository extends EntityRepository
{
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
    public function getOpportunitiesByState(AclHelper $aclHelper)
    {
        $dateEnd = new \DateTime('now', new \DateTimeZone('UTC'));
        $dateStart = new \DateTime(
            $dateEnd->format('Y') . '-01-' . ((ceil($dateEnd->format('n') / 3) - 1) * 3 + 1),
            new \DateTimeZone('UTC')
        );
        $data = $this->getOpportunitiesDataByState($aclHelper, $dateStart, $dateEnd);

        $resultData = [];
        $labels = [];

        foreach ($data as $index => $dataValue) {
            $resultData[$index] = [$index, (double)$dataValue['budget']];
            $labels[$index] = $dataValue['label'];
        }

        return ['data' => $resultData, 'labels' => $labels];
    }

    /**
     * Get funnel chart data
     *
     * @param $entityClass
     * @param $fieldName
     * @param array $visibleSteps
     * @param array $additionalNozzle
     * @param AclHelper $aclHelper
     * @return array
     *   items - array of data
     *     key - labels
     *     value - sum of budgets
     *   nozzleSteps - array with nozzle steps labels
     */
    public function getFunnelChartData(
        $entityClass,
        $fieldName,
        $visibleSteps = [],
        $additionalNozzle = [],
        AclHelper $aclHelper = null
    ) {
        $resultData = $this->getEntityManager()
            ->getRepository('OroWorkflowBundle:WorkflowItem')
            ->getFunnelChartData(
                $entityClass,
                $fieldName,
                $visibleSteps,
                $aclHelper
            );
        $nozzleStepsLabels = [];

        if (!empty($additionalNozzle)) {
            $this->setAdditionalNozzleSteps($additionalNozzle, $aclHelper, $resultData, $nozzleStepsLabels);
        }

        $sum = 0;
        foreach ($resultData as $data) {
            $sum += $data;
        }

        return [
            'items' => $sum > 0 ? $resultData : [],
            'nozzleSteps' => $nozzleStepsLabels
        ];
    }

    /**
     * Get streamline funnel chart data
     *
     * @param $entityClass
     * @param $fieldName
     * @param AclHelper $aclHelper
     * @return array
     *   items - array of data
     *     key - labels
     *     value - sum of budgets
     *   nozzleSteps - array with nozzle steps labels
     */
    public function getStreamlineFunnelChartData($entityClass, $fieldName, AclHelper $aclHelper = null)
    {
        $data = $this->getFunnelChartData($entityClass, $fieldName, $aclHelper);

        $dataKeys = array_keys($data['items']);
        for ($i = count($dataKeys) - 1; $i >= 0; $i--) {
            if (isset($dataKeys[$i - 1])) {
                $data['items'][$dataKeys[$i - 1]] += $data['items'][$dataKeys[$i]];
            }
        }

        return $data;
    }

    /**
     * @param AclHelper $aclHelper
     * @param $dateStart
     * @param $dateEnd
     * @return array
     */
    protected function getOpportunitiesDataByState(AclHelper $aclHelper, $dateStart = null, $dateEnd = null)
    {
        $qb = $this->createQueryBuilder('opp');
        $qb->select('opp_status.name', 'opp_status.label', 'SUM(opp.budgetAmount) as budget')
            ->join('opp.status', 'opp_status');
        if ($dateStart && $dateEnd) {
            $qb->where($qb->expr()->between('opp.createdAt', ':dateFrom', ':dateTo'))
                ->setParameter('dateFrom', $dateStart)
                ->setParameter('dateTo', $dateEnd);
        }
        $qb->groupBy('opp_status.name');

        return $aclHelper->apply($qb)
            ->getArrayResult();
    }

    /**
     * Set additional nozzle steps data for funnel chart
     *
     * @param $additionalNozzle
     * @param AclHelper $aclHelper
     * @param $resultData
     * @param $nozzleStepsLabels
     */
    protected function setAdditionalNozzleSteps(
        $additionalNozzle,
        AclHelper $aclHelper,
        &$resultData,
        &$nozzleStepsLabels
    ) {
        $dateEnd = new \DateTime('now', new \DateTimeZone('UTC'));
        $dateStart = new \DateTime(
            $dateEnd->format('Y') . '-01-' . ((ceil($dateEnd->format('n') / 3) - 1) * 3 + 1),
            new \DateTimeZone('UTC')
        );

        $budgetString = 'SUM(CASE ';
        foreach ($additionalNozzle as $stepName => $field) {
            $budgetString .= 'WHEN opp_status.name = \'' . $stepName . '\' THEN opp.' . $field . ' ';
        }
        $budgetString .= 'else 0 end) ';

        $qb = $this->createQueryBuilder('opp');
        $qb->select('opp_status.name', 'opp_status.label', $budgetString . 'as budget')
            ->join('opp.status', 'opp_status')
            ->where($qb->expr()->between('opp.createdAt', ':dateFrom', ':dateTo'))
            ->setParameter('dateFrom', $dateStart)
            ->setParameter('dateTo', $dateEnd);

        $qb->groupBy('opp_status.name');

        $states = $aclHelper->apply($qb)
            ->getArrayResult();

        foreach (array_keys($additionalNozzle) as $nozzle) {
            foreach ($states as $state) {
                if ($state['name'] == $nozzle) {
                    $resultData[$state['label']] = (double)$state['budget'];
                    $nozzleStepsLabels[] = $state['label'];
                }
            }
        }
    }
}
