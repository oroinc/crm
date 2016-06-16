<?php

namespace OroCRM\Bundle\MagentoBundle\Dashboard;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\DashboardBundle\Filter\DateFilterProcessor;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use OroCRM\Bundle\MagentoBundle\Entity\Repository\CartRepository;

class SalesFlowB2CProvider
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var WorkflowManager */
    protected $workflowManager;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var DateFilterProcessor */
    protected $dateFilterProcessor;
    
    /** @var array */
    protected static $excludedSteps = [
        'converted_to_opportunity',
        'abandoned'
    ];
    
    /**
     * @param ManagerRegistry     $registry
     * @param WorkflowManager     $workflowManager
     * @param AclHelper           $aclHelper
     * @param DateFilterProcessor $processor
     */
    public function __construct(
        ManagerRegistry $registry,
        WorkflowManager $workflowManager,
        AclHelper $aclHelper,
        DateFilterProcessor $processor
    ) {
        $this->registry              = $registry;
        $this->workflowManager        = $workflowManager;
        $this->aclHelper             = $aclHelper;
        $this->dateFilterProcessor   = $processor;
    }

    /**
     * @param array $dateRange
     *
     * @return array
     */
    public function getSalesFlowB2CData(array $dateRange)
    {
        $workflow = $this->workflowManager
            ->getApplicableWorkflowByEntityClass('OroCRM\Bundle\MagentoBundle\Entity\Cart');
        $shoppingCartRepository = $this->getCartRepository();
        if (!$workflow) {
            return ['items' => [], 'nozzleSteps' => []];
        }
        $steps = $workflow->getStepManager()->getOrderedSteps();
        // regular and final steps should be calculated separately
        $regularSteps = [];
        $finalSteps   = [];
        foreach ($steps as $step) {
            if (!in_array($step->getName(), static::$excludedSteps)) {
                if ($step->isFinal()) {
                    $finalSteps[] = $step->getName();
                } else {
                    $regularSteps[] = $step->getName();
                }
            }
        }

        // regular steps should be calculated for whole period, final steps - for specified period
        $regularStepsQB = $shoppingCartRepository->getStepDataQB('cart', $regularSteps, static::$excludedSteps);
        $regularStepsResult = $regularStepsQB->getQuery()->getArrayResult();
        foreach ($regularStepsResult as $record) {
            $regularStepsData[$record['workflowStepName']] = $record['total'] ? (float)$record['total'] : 0;
        }

        $finalStepsQB   = $shoppingCartRepository->getStepDataQB('cart1', $finalSteps, static::$excludedSteps);
        $this->dateFilterProcessor->process($finalStepsQB, $dateRange, 'cart1.createdAt');
        $this->aclHelper->apply($finalStepsQB);
        $finalStepsResult = $finalStepsQB->getQuery()->getArrayResult();
        foreach ($finalStepsResult as $record) {
            $finalStepsData[$record['workflowStepName']] = $record['total'] ? (float)$record['total'] : 0;
        }
        // final calculation
        $data = [];
        foreach ($steps as $step) {
            $stepName = $step->getName();
            if (!in_array($stepName, static::$excludedSteps)) {
                if ($step->isFinal()) {
                    $stepValue = isset($finalStepsData[$stepName]) ? $finalStepsData[$stepName] : 0;
                    $data[]    = ['label' => $step->getLabel(), 'value' => $stepValue, 'isNozzle' => true];//'2016-06-12 21:00:00' - start of the week
                } else {
                    $stepValue = isset($regularStepsData[$stepName]) ? $regularStepsData[$stepName] : 0;
                    $data[]    = ['label' => $step->getLabel(), 'value' => $stepValue, 'isNozzle' => false];
                }
            }
        }

        return $data;
    }

    /**
     * @return CartRepository
     */
    protected function getCartRepository()
    {
        return $this->registry->getRepository('OroCRMMagentoBundle:Cart');
    }
}
