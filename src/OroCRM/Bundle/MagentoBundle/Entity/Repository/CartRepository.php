<?php

namespace OroCRM\Bundle\MagentoBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class CartRepository extends EntityRepository
{
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
     *     key - label
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
        $nozzleStepsLabels = [];
        $workflowItemRepo =  $this->getEntityManager()
            ->getRepository('OroWorkflowBundle:WorkflowItem');

        $resultData = $workflowItemRepo
            ->getFunnelChartData(
                $entityClass,
                $fieldName,
                $visibleSteps,
                $aclHelper
            );

        if (!empty($additionalNozzle)) {
            $dateEnd = new \DateTime('now', new \DateTimeZone('UTC'));
            $dateStart = new \DateTime(
                $dateEnd->format('Y') . '-01-' . ((ceil($dateEnd->format('n') / 3) - 1) * 3 + 1),
                new \DateTimeZone('UTC')
            );

            $nozzleData = $workflowItemRepo
                ->getFunnelChartData(
                    $entityClass,
                    $fieldName,
                    $additionalNozzle,
                    $aclHelper,
                    $dateStart,
                    $dateEnd
                );
            $resultData = array_merge($resultData, $nozzleData);
            $nozzleStepsLabels = array_keys($nozzleData);
        }

        return [
            'items' => $resultData,
            'nozzleSteps' => $nozzleStepsLabels
        ];
    }
}
