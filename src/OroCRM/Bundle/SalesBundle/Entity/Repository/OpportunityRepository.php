<?php

namespace OroCRM\Bundle\SalesBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class OpportunityRepository extends EntityRepository
{
    /**
     * Get opportunities by state by last month
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

    /**
     * Get funnel chart data
     *
     * @param $entityClass
     * @param $fieldName
     * @param AclHelper $aclHelper
     * @return array
     *   key - label of lead status
     *   value - sum of budgets
     */
    public function getFunnelChartData($entityClass, $fieldName, AclHelper $aclHelper = null)
    {
        $resultData = [];
        $dateEnd = new \DateTime('now', new \DateTimeZone('UTC'));
        $dateStart = clone $dateEnd;
        $dateStart = $dateStart->sub(new \DateInterval('P1M'));
        $definition = $this->getEntityManager()
            ->getRepository('OroWorkflowBundle:WorkflowDefinition')
            ->findByEntityClass($entityClass);

        if (isset($definition[0])) {
            $workFlow = $definition[0];
            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb->select('wi.currentStepName', 'SUM(opp.' . $fieldName .') as budget')
                ->from($entityClass, 'opp')
                ->join('OroWorkflowBundle:WorkflowBindEntity', 'wbe', 'WITH', 'wbe.entityId = opp.id')
                ->join('wbe.workflowItem', 'wi')
                ->where($qb->expr()->between('opp.createdAt', ':dateFrom', ':dateTo'))
                ->setParameter('dateFrom', $dateStart)
                ->setParameter('dateTo', $dateEnd)
                ->andWhere('wi.workflowName = :workFlowName')
                ->setParameter('workFlowName', $workFlow->getName())
                ->groupBy('wi.currentStepName');

            $query = $aclHelper ? $aclHelper->apply($qb) : $qb->getQuery();
            $data = $query->getArrayResult();

            if (!empty($data)) {
                foreach ($workFlow->getConfiguration()['steps'] as $stepName => $config) {
                    foreach ($data as $dataValue) {
                        if ($dataValue['currentStepName'] == $stepName) {
                            $resultData[$config['label']] = (double)$dataValue['budget'];
                        }
                    }

                    if (!isset($resultData[$config['label']])) {
                        $resultData[$config['label']] = 0;
                    }
                }
            }
        }

        return $resultData;
    }

    /**
     * Get streamline funnel chart data
     *
     * @param $entityClass
     * @param $fieldName
     * @param AclHelper $aclHelper
     * @return array
     *   key - label of lead status
     *   value - sum of budgets
     */
    public function getStreamlineFunnelChartData($entityClass, $fieldName, AclHelper $aclHelper = null)
    {
        $data = $this->getFunnelChartData($entityClass, $fieldName, $aclHelper);

        $dataKeys = array_keys($data);

        for ($i = count($dataKeys) - 1; $i >= 0; $i--) {
            if (isset($dataKeys[$i - 1])) {
                $data[$dataKeys[$i - 1]] += $data[$dataKeys[$i]];
            }
        }

        return $data;
    }
}
