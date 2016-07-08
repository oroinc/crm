<?php

namespace OroCRM\Bundle\SalesBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\WorkflowBundle\Helper\WorkflowQueryHelper;
use Oro\Bundle\WorkflowBundle\Model\Workflow;

class SalesFunnelRepository extends EntityRepository
{
    /**
     * @var array
     */
    protected $excludedSteps = [
        'new_lead',
        'disqualified_lead',
        'lost_opportunity',
    ];

    /**
     * @param  \DateTime $dateFrom
     * @param  \DateTime $dateTo
     * @param  Workflow  $workflow
     * @param  array     $customStepCalculations
     * @param  AclHelper $aclHelper
     *
     * @return array
     */
    public function getFunnelChartData(
        \DateTime $dateFrom = null,
        \DateTime $dateTo = null,
        Workflow $workflow = null,
        array $customStepCalculations = [],
        AclHelper $aclHelper = null
    ) {
        $data = array();

        if (!$workflow) {
            return $data;
        }

        $steps = $workflow->getStepManager()->getOrderedSteps();

        // regular and final steps should be calculated separately
        $regularSteps = [];
        $finalSteps   = [];
        foreach ($steps as $step) {
            if (!in_array($step->getName(), $this->excludedSteps)) {
                if ($step->isFinal()) {
                    $finalSteps[] = $step->getName();
                } else {
                    $regularSteps[] = $step->getName();
                }
            }
        }

        // regular steps should be calculated for whole period, final steps - for specified period
        $regularStepsData = $this->getStepData($regularSteps, null, null, $customStepCalculations, $aclHelper);
        $finalStepsData   = $this->getStepData($finalSteps, $dateFrom, $dateTo, $customStepCalculations, $aclHelper);

        foreach ($steps as $step) {
            $stepName = $step->getName();
            if (!in_array($stepName, $this->excludedSteps)) {
                $stepLabel = $step->getLabel();
                if ($step->isFinal()) {
                    $dataValue = isset($finalStepsData[$stepName]) ? $finalStepsData[$stepName] : 0;
                    $data[]    = ['value' => $dataValue, 'label' => $stepLabel, 'isNozzle' => true];
                } else {
                    $dataValue = isset($regularStepsData[$stepName]) ? $regularStepsData[$stepName] : 0;
                    $data[]    = ['value' => $dataValue, 'label' => $stepLabel, 'isNozzle' => false];
                }
            }
        }

        return $data;
    }

    /**
     * @param  array     $steps
     * @param  \DateTime $dateFrom
     * @param  \DateTime $dateTo
     * @param  array     $customStepCalculations
     * @param  AclHelper $aclHelper
     *
     * @return array
     */
    protected function getStepData(
        array $steps,
        \DateTime $dateFrom = null,
        \DateTime $dateTo = null,
        array $customStepCalculations = [],
        AclHelper $aclHelper = null
    ) {
        $stepData = [];

        if (!$steps) {
            return $stepData;
        }

        $budgetAmountQueryBuilder = $this->getTemplateQueryBuilder($dateFrom, $dateTo)
            ->addSelect('SUM(opportunity.budgetAmount) as budgetAmount');
        $budgetAmountQueryBuilder
            ->andWhere($budgetAmountQueryBuilder->expr()->in('workflowStep.name', $steps));
        $budgetAmountQuery = $this->getQuery($budgetAmountQueryBuilder, $aclHelper);

        foreach ($budgetAmountQuery->getArrayResult() as $record) {
            $stepData[$record['workflowStepName']] = $record['budgetAmount'] ? (float)$record['budgetAmount'] : 0;
        }

        foreach ($customStepCalculations as $step => $field) {
            if (!in_array($step, $steps)) {
                continue;
            }

            $customStepQueryBuilder = $this->getTemplateQueryBuilder($dateFrom, $dateTo)
                ->addSelect('SUM(' . $field . ') as value')
                ->andWhere('workflowStep.name = :workflowStep')
                ->setParameter('workflowStep', $step);
            $customStepQueryBuilder
                ->andWhere($customStepQueryBuilder->expr()->in('workflowStep.name', $steps));
            $customStepQuery = $this->getQuery($customStepQueryBuilder, $aclHelper);

            foreach ($customStepQuery->getArrayResult() as $record) {
                $stepData[$record['workflowStepName']] = $record['value'] ? (float)$record['value'] : 0;
            }
        }

        return $stepData;
    }

    /**
     * @param  QueryBuilder $queryBuilder
     * @param  AclHelper    $aclHelper
     *
     * @return Query
     */
    protected function getQuery(QueryBuilder $queryBuilder, AclHelper $aclHelper = null)
    {
        return $aclHelper ? $aclHelper->apply($queryBuilder) : $queryBuilder->getQuery();
    }

    /**
     * @param  \DateTime $dateFrom
     * @param  \DateTime $dataTo
     *
     * @return QueryBuilder
     */
    protected function getTemplateQueryBuilder(\DateTime $dateFrom = null, \DateTime $dataTo = null)
    {
        $queryBuilder = $this->createQueryBuilder('funnel')
            ->select('workflowStep.name as workflowStepName')
            ->join('funnel.opportunity', 'opportunity');
        WorkflowQueryHelper::addQuery($queryBuilder);
        $queryBuilder->groupBy('workflowStep.name');

        if ($dateFrom && $dataTo) {
            $queryBuilder
                ->where($queryBuilder->expr()->between('funnel.createdAt', ':dateFrom', ':dateTo'))
                ->setParameter('dateFrom', $dateFrom)
                ->setParameter('dateTo', $dataTo);
        }

        return $queryBuilder;
    }
}
