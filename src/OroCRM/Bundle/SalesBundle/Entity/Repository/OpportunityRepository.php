<?php

namespace OroCRM\Bundle\SalesBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\WorkflowBundle\Entity\Repository\WorkflowStepRepository;
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
     * @param array $visibleSteps
     * @param array $nozzleStatuses
     * @param AclHelper $aclHelper
     * @return array
     *   items - array of data
     *     key - labels
     *     value - sum of budgets
     *   nozzleSteps - array with nozzle steps labels
     */
    public function getFunnelChartData(
        array $visibleSteps = array(),
        array $nozzleStatuses = array(),
        AclHelper $aclHelper = null
    ) {
        // calculate step data by workflow steps
        $resultData = $this->getStepResultData($visibleSteps, $aclHelper);

        // add data for custom nozzle statuses
        $nozzleStatusLabels = array();
        if ($nozzleStatuses) {
            list($resultData, $nozzleStatusLabels)
                = $this->addNozzleStatusData($resultData, $nozzleStatuses, $aclHelper);
        }

        $sum = 0;
        foreach ($resultData as $data) {
            $sum += $data;
        }

        return [
            'items' => $sum > 0 ? $resultData : array(),
            'nozzleSteps' => $nozzleStatusLabels
        ];
    }

    /**
     * @return WorkflowStep[]
     */
    protected function getWorkflowStepsByName()
    {
        if (null === $this->workflowStepsByName) {
            /** @var WorkflowStepRepository $workflowStepRepository */
            $workflowStepRepository = $this->getEntityManager()->getRepository('OroWorkflowBundle:WorkflowStep');
            $this->workflowStepsByName = $workflowStepRepository->findByRelatedEntityByName($this->getEntityName());
        }

        return $this->workflowStepsByName;
    }

    /**
     * @param AclHelper $aclHelper
     * @return array
     */
    protected function getStepData(AclHelper $aclHelper = null)
    {
        $queryBuilder = $this->createQueryBuilder('opportunity')
            ->select('workflowStep.name as workflowStepName', 'SUM(opportunity.budgetAmount) as budget')
            ->join('opportunity.workflowStep', 'workflowStep')
            ->groupBy('workflowStep.name');

        if ($aclHelper) {
            $query = $aclHelper->apply($queryBuilder);
        } else {
            $query = $queryBuilder->getQuery();
        }

        $stepData = [];
        foreach ($query->getArrayResult() as $record) {
            $stepData[$record['workflowStepName']] = $record['budget'];
        }

        return $stepData;
    }

    /**
     * @param array $visibleSteps
     * @param AclHelper $aclHelper
     * @return array
     */
    protected function getStepResultData(array $visibleSteps = array(), AclHelper $aclHelper = null)
    {
        $resultData = [];
        $stepData = $this->getStepData($aclHelper);
        if (!empty($stepData) || !empty($visibleSteps)) {
            $workflowSteps = $this->getWorkflowStepsByName();

            if (!empty($visibleSteps)) {
                $steps = $visibleSteps;
            } else {
                $steps = array_keys($workflowSteps);
            }

            foreach ($steps as $stepName) {
                if (!array_key_exists($stepName, $workflowSteps)) {
                    continue;
                }

                $workflowStep = $workflowSteps[$stepName];
                $stepLabel = $workflowStep->getLabel();

                if (array_key_exists($stepName, $stepData)) {
                    $resultData[$stepLabel] = (float)$stepData[$stepName];
                } else {
                    $resultData[$stepLabel] = 0;
                }
            }
        }

        return $resultData;
    }

    /**
     * Get streamline funnel chart data
     *
     * @param AclHelper $aclHelper
     * @return array
     *   items - array of data
     *     key - labels
     *     value - sum of budgets
     *   nozzleSteps - array with nozzle steps labels
     */
    public function getStreamlineFunnelChartData(AclHelper $aclHelper = null)
    {
        $data = $this->getFunnelChartData($aclHelper);

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
     * @param array $resultData
     * @param array $additionalNozzle
     * @param AclHelper $aclHelper
     * @return array
     */
    protected function addNozzleStatusData(
        array $resultData,
        array $additionalNozzle,
        AclHelper $aclHelper
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
            ->groupBy('opp_status.name')
            ->setParameter('dateFrom', $dateStart)
            ->setParameter('dateTo', $dateEnd);

        $statuses = $aclHelper->apply($qb)
            ->getArrayResult();

        $nozzleStatusLabels = [];
        foreach (array_keys($additionalNozzle) as $nozzle) {
            foreach ($statuses as $state) {
                if ($state['name'] == $nozzle) {
                    $resultData[$state['label']] = (double)$state['budget'];
                    $nozzleStatusLabels[] = $state['label'];
                }
            }
        }

        return array($resultData, $nozzleStatusLabels);
    }
}
