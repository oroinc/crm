<?php

namespace OroCRM\Bundle\MagentoBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\WorkflowBundle\Entity\Repository\WorkflowStepRepository;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowStep;

class CartRepository extends EntityRepository
{
    /**
     * @var WorkflowStep[]
     */
    protected $workflowStepsByName;

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
     * Get funnel chart data
     *
     * @param array $visibleSteps
     * @param array $nozzleSteps
     * @param AclHelper $aclHelper
     * @return array
     *   items - array of data
     *     key - label
     *     value - sum of budgets
     *   nozzleSteps - array with nozzle steps labels
     */
    public function getFunnelChartData(
        $visibleSteps = [],
        $nozzleSteps = [],
        AclHelper $aclHelper = null
    ) {
        $nozzleStepsLabels = [];
        $resultData = $this->getStepResultData($visibleSteps, $aclHelper);

        if (!empty($nozzleSteps)) {
            $dateEnd = new \DateTime('now', new \DateTimeZone('UTC'));
            $dateStart = new \DateTime(
                $dateEnd->format('Y') . '-01-' . ((ceil($dateEnd->format('n') / 3) - 1) * 3 + 1),
                new \DateTimeZone('UTC')
            );

            $nozzleData = $this->getStepResultData($nozzleSteps, $aclHelper, $dateStart, $dateEnd);
            $resultData = array_merge($resultData, $nozzleData);
            $nozzleStepsLabels = array_keys($nozzleData);
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
     * @param AclHelper $aclHelper
     * @param \DateTime $dateStart
     * @param \DateTime $dateEnd
     * @return array
     */
    protected function getStepData(
        AclHelper $aclHelper = null,
        \DateTime $dateStart = null,
        \DateTime $dateEnd = null
    ) {
        $queryBuilder = $this->createQueryBuilder('cart')
            ->select('workflowStep.name as workflowStepName', 'SUM(cart.grandTotal) as total')
            ->join('cart.workflowStep', 'workflowStep')
            ->groupBy('workflowStep.name');

        if ($dateStart && $dateEnd) {
            $queryBuilder->andWhere($queryBuilder->expr()->between('cart.createdAt', ':dateFrom', ':dateTo'))
                ->setParameter('dateFrom', $dateStart)
                ->setParameter('dateTo', $dateEnd);
        }

        if ($aclHelper) {
            $query = $aclHelper->apply($queryBuilder);
        } else {
            $query = $queryBuilder->getQuery();
        }

        $stepData = [];
        foreach ($query->getArrayResult() as $record) {
            $stepData[$record['workflowStepName']] = $record['total'];
        }

        return $stepData;
    }

    /**
     * @param array $requestedSteps
     * @param AclHelper $aclHelper
     * @param \DateTime $dateStart
     * @param \DateTime $dateEnd
     * @return array
     */
    protected function getStepResultData(
        array $requestedSteps = array(),
        AclHelper $aclHelper = null,
        \DateTime $dateStart = null,
        \DateTime $dateEnd = null
    ) {
        $resultData = [];
        $stepData = $this->getStepData($aclHelper, $dateStart, $dateEnd);
        if (!empty($stepData) || !empty($requestedSteps)) {
            $workflowSteps = $this->getWorkflowStepsByName();

            if (!empty($requestedSteps)) {
                $steps = $requestedSteps;
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
}
