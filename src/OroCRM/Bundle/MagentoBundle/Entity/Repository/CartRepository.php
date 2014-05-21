<?php

namespace OroCRM\Bundle\MagentoBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;

class CartRepository extends EntityRepository
{
    /**
     * @var array
     */
    protected $excludedSteps = [
        'converted_to_opportunity',
        'abandoned',
    ];

    /**
     * @var array
     */
    protected $excludedStatuses = [
        'purchased',
        'expired'
    ];

    /**
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * @param Workflow  $workflow
     * @param AclHelper $aclHelper
     *
     * @return array
     */
    public function getFunnelChartData(
        \DateTime $dateFrom,
        \DateTime $dateTo,
        Workflow $workflow = null,
        AclHelper $aclHelper = null
    ) {
        if (!$workflow) {
            return array('items' => array(), 'nozzleSteps' => array());
        }

        $steps = $workflow->getStepManager()->getOrderedSteps();

        // regular and final steps should be calculated separately
        $regularSteps = array();
        $finalSteps   = array();
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
        $regularStepsData = $this->getStepData($regularSteps, null, null, $aclHelper);
        $finalStepsData   = $this->getStepData($finalSteps, $dateFrom, $dateTo, $aclHelper);

        // final calculation
        $data = array();
        foreach ($steps as $step) {
            $stepName  = $step->getName();
            if (!in_array($stepName, $this->excludedSteps)) {
                if ($step->isFinal()) {
                    $stepValue = isset($finalStepsData[$stepName]) ? $finalStepsData[$stepName] : 0;
                    $data[] = array('label' => $step->getLabel(), 'value' => $stepValue, 'isNozzle' => true);
                } else {
                    $stepValue = isset($regularStepsData[$stepName]) ? $regularStepsData[$stepName] : 0;
                    $data[] = array('label' => $step->getLabel(), 'value' => $stepValue, 'isNozzle' => false);
                }
            }
        }
        return $data;
    }

    /**
     * @param array     $steps
     * @param AclHelper $aclHelper
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     *
     * @return array
     */
    protected function getStepData(
        array $steps,
        \DateTime $dateFrom = null,
        \DateTime $dateTo = null,
        AclHelper $aclHelper = null
    ) {
        $stepData = array();

        if (!$steps) {
            return $stepData;
        }

        $queryBuilder = $this->createQueryBuilder('cart')
            ->select('workflowStep.name as workflowStepName', 'SUM(cart.grandTotal) as total')
            ->leftJoin('cart.status', 'status')
            ->join('cart.workflowStep', 'workflowStep')
            ->groupBy('workflowStep.name');

        $queryBuilder->where($queryBuilder->expr()->in('workflowStep.name', $steps));

        if ($dateFrom && $dateTo) {
            $queryBuilder->andWhere($queryBuilder->expr()->between('cart.createdAt', ':dateFrom', ':dateTo'))
                ->setParameter('dateFrom', $dateFrom)
                ->setParameter('dateTo', $dateTo);
        }

        if ($this->excludedStatuses) {
            $queryBuilder->andWhere($queryBuilder->expr()->notIn('status.name', $this->excludedStatuses));
        }

        if ($aclHelper) {
            $query = $aclHelper->apply($queryBuilder);
        } else {
            $query = $queryBuilder->getQuery();
        }

        foreach ($query->getArrayResult() as $record) {
            $stepData[$record['workflowStepName']] = $record['total'] ? (float)$record['total'] : 0;
        }

        return $stepData;
    }

    /**
     * Update statuses for carts to 'expired'
     *
     * @param array $ids
     */
    public function markExpired(array $ids)
    {
        $em = $this->getEntityManager();
        foreach ($ids as $id) {
            /** @var Cart $cart */
            $cart = $em->getReference($this->getEntityName(), $id);
            $cart->setStatus($em->getReference('OroCRMMagentoBundle:CartStatus', 'expired'));
        }

        $em->flush();
    }

    /**
     * Returns iterator for fetching IDs pairs by channel and given status
     * Each item in iteration will be array with following data:
     * [
     *      'id'        => ENTITY_ID,
     *      'originId'  => ENTITY_ORIGIN_ID
     * ]
     *
     * @param Channel $channel
     * @param string  $status
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getCartsByChannelIdsIterator(Channel $channel, $status = 'open')
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c.id, c.originId')
            ->leftJoin('c.status', 'cstatus')
            ->andWhere('c.channel = :channel')
            ->andWhere('cstatus.name = :statusName')
            ->setParameter('channel', $channel)
            ->setParameter('statusName', $status);

        return new BufferedQueryResultIterator($qb);
    }
}
