<?php

namespace OroCRM\Bundle\SalesBundle\Entity\Repository;

use DateTime;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

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
     * @param  array     $dateRange
     * @return array
     */
    public function getOpportunitiesByStatus(AclHelper $aclHelper, $dateRange)
    {
        $dateEnd = $dateRange['end'];
        $dateStart = $dateRange['start'];

        return $this->getOpportunitiesDataByStatus($aclHelper, $dateStart, $dateEnd);
    }

    /**
     * @param  AclHelper $aclHelper
     * @param $dateStart
     * @param $dateEnd
     * @return array
     */
    protected function getOpportunitiesDataByStatus(AclHelper $aclHelper, $dateStart = null, $dateEnd = null)
    {
        // select statuses
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('status.name, status.label')
            ->from('OroCRMSalesBundle:OpportunityStatus', 'status')
            ->orderBy('status.name', 'ASC');

        $resultData = array();
        $data = $qb->getQuery()->getArrayResult();
        foreach ($data as $status) {
            $name = $status['name'];
            $label = $status['label'];
            $resultData[$name] = array(
                'name' => $name,
                'label' => $label,
                'budget' => 0,
            );
        }

        // select opportunity data
        $qb = $this->createQueryBuilder('opportunity');
        $qb->select('IDENTITY(opportunity.status) as name, SUM(opportunity.budgetAmount) as budget')
            ->groupBy('opportunity.status');

        if ($dateStart && $dateEnd) {
            $qb->where($qb->expr()->between('opportunity.createdAt', ':dateFrom', ':dateTo'))
                ->setParameter('dateFrom', $dateStart)
                ->setParameter('dateTo', $dateEnd);
        }
        $groupedData = $aclHelper->apply($qb)->getArrayResult();

        foreach ($groupedData as $statusData) {
            $status = $statusData['name'];
            $budget = (float)$statusData['budget'];
            if ($budget) {
                $resultData[$status]['budget'] = $budget;
            }
        }

        return $resultData;
    }

    /**
     * @param AclHelper $aclHelper
     * @param DateTime $start
     * @param DateTime $end
     *
     * @return int
     */
    public function getOpportunitiesCount(AclHelper $aclHelper, DateTime $start, DateTime $end)
    {
        $qb = $this->createOpportunitiesCountQb($start, $end);

        return $aclHelper->apply($qb)->getSingleScalarResult();
    }

    /**
     * @param AclHelper $aclHelper
     * @param DateTime $start
     * @param DateTime $end
     *
     * @return int
     */
    public function getNewOpportunitiesCount(AclHelper $aclHelper, DateTime $start, DateTime $end)
    {
        $qb = $this->createOpportunitiesCountQb($start, $end)
            ->andWhere('o.closeDate IS NULL');

        return $aclHelper->apply($qb)->getSingleScalarResult();
    }

    /**
     * @param DateTime $start
     * @param DateTime $end
     *
     * @return QueryBuilder
     */
    public function createOpportunitiesCountQb(DateTime $start, DateTime $end)
    {
        $qb = $this->createQueryBuilder('o');

        $qb
            ->select('COUNT(o.id)')
            ->andWhere($qb->expr()->between('o.createdAt', ':start', ':end'))
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        return $qb;
    }

    /**
     * @param AclHelper $aclHelper
     * @param DateTime $start
     * @param DateTime $end
     *
     * @return double
     */
    public function getTotalServicePipelineAmount(AclHelper $aclHelper, DateTime $start, DateTime $end)
    {
        $qb = $this->createQueryBuilder('o');

        $qb
            ->select('SUM(o.budgetAmount)')
            ->andWhere($qb->expr()->between('o.createdAt', ':start', ':end'))
            ->andWhere('o.closeDate IS NULL')
            ->andWhere('o.status = :status')
            ->andWhere('o.probability != 0')
            ->andWhere('o.probability != 1')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('status', 'in_progress');

        return $aclHelper->apply($qb)->getSingleScalarResult();
    }

    /**
     * @param AclHelper $aclHelper
     * @param DateTime $start
     * @param DateTime $end
     *
     * @return double
     */
    public function getTotalServicePipelineAmountInProgress(
        AclHelper $aclHelper,
        DateTime $start,
        DateTime $end
    ) {
        $qb = $this->createQueryBuilder('o');

        $qb
            ->select('SUM(o.budgetAmount)')
            ->andWhere($qb->expr()->between('o.createdAt', ':start', ':end'))
            ->andWhere('o.status = :status')
            ->andWhere('o.probability != 0')
            ->andWhere('o.probability != 1')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('status', 'in_progress');

        return $aclHelper->apply($qb)->getSingleScalarResult();
    }

    /**
     * @param AclHelper $aclHelper
     * @param DateTime $start
     * @param DateTime $end
     *
     * @return double
     */
    public function getWeightedPipelineAmount(AclHelper $aclHelper, DateTime $start, DateTime $end)
    {
        $qb = $this->createQueryBuilder('o');

        $qb
            ->select('SUM(o.budgetAmount * o.probability)')
            ->andWhere($qb->expr()->between('o.createdAt', ':start', ':end'))
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        return $aclHelper->apply($qb)->getSingleScalarResult();
    }

    /**
     * @param AclHelper $aclHelper
     * @param DateTime $start
     * @param DateTime $end
     *
     * @return double
     */
    public function getOpenWeightedPipelineAmount(AclHelper $aclHelper, DateTime $start, DateTime $end)
    {
        $qb = $this->createQueryBuilder('o');

        $qb
            ->select('SUM(o.budgetAmount * o.probability)')
            ->andWhere($qb->expr()->between('o.createdAt', ':start', ':end'))
            ->andWhere('o.closeDate IS NULL')
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        return $aclHelper->apply($qb)->getSingleScalarResult();
    }
}
