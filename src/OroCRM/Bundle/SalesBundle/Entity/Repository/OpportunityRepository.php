<?php

namespace OroCRM\Bundle\SalesBundle\Entity\Repository;

use DateTime;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\DataAuditBundle\Loggable\LoggableManager;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Component\DoctrineUtils\ORM\QueryUtils;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class OpportunityRepository extends EntityRepository
{
    const OPPORTUNITY_STATE_IN_PROGRESS      = 'In Progress';
    const OPPORTUNITY_STATE_IN_PROGRESS_CODE = 'in_progress';

    /**
     * Get opportunities by state by current quarter
     *
     * @param           $aclHelper AclHelper
     * @param  array    $dateRange
     * @param  array    $states
     * @param int[]     $owners
     *
     * @param AclHelper $aclHelper
     * @param array     $dateRange
     * @param array     $states
     * @param int[]     $owners
     *
     * @return array
     */
    public function getOpportunitiesByStatus(AclHelper $aclHelper, $dateRange, $states, $owners = [])
    {
        $dateEnd   = $dateRange['end'];
        $dateStart = $dateRange['start'];

        return $this->getOpportunitiesDataByStatus($aclHelper, $dateStart, $dateEnd, $states, $owners);
    }

    /**
     * @param string $alias
     * @param string $orderBy
     * @param string $direction
     *
     * @return QueryBuilder
     *
     */
    public function getGroupedOpportunitiesByStatusQB(
        $alias,
        $orderBy = 'budget',
        $direction = 'DESC'
    ) {
        $statusClass = ExtendHelper::buildEnumValueClassName('opportunity_status');
        $repository  = $this->getEntityManager()->getRepository($statusClass);

        $qb = $repository->createQueryBuilder('s')
            ->select(
                's.name as label',
                sprintf('COUNT(%s.id) as quantity', $alias),
                // Use close revenue for calculating budget for opportunities with won statuses
                sprintf(
                    "SUM(
                        CASE WHEN s.id = 'won'
                            THEN
                                (CASE WHEN %s.closeRevenue IS NOT NULL THEN %s.closeRevenue ELSE 0 END)
                            ELSE
                                (CASE WHEN %s.budgetAmount IS NOT NULL THEN %s.budgetAmount ELSE 0 END)
                        END
                    ) as budget",
                    $alias,
                    $alias,
                    $alias,
                    $alias
                )
            )
            ->leftJoin('OroCRMSalesBundle:Opportunity', $alias, 'WITH', sprintf('%s.status = s', $alias))
            ->groupBy('s.name')
            ->orderBy($orderBy, $direction);

        return $qb;
    }

    /**
     * @param  AclHelper $aclHelper
     * @param            $dateStart
     * @param            $dateEnd
     * @param array      $states
     * @param int[]      $owners
     *
     * @return array
     */
    protected function getOpportunitiesDataByStatus(
        AclHelper $aclHelper,
        $dateStart = null,
        $dateEnd = null,
        $states = [],
        $owners = []
    ) {
        foreach ($states as $key => $name) {
            $resultData[$key] = [
                'name'   => $key,
                'label'  => $name,
                'budget' => 0,
            ];
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

        if ($owners) {
            QueryUtils::applyOptimizedIn($qb, 'opportunity.owner', $owners);
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
     * @param array       $ownerIds
     * @param DateTime    $date
     * @param AclHelper   $aclHelper
     *
     * @param string|null $start
     * @param string|null $end
     *
     * @return mixed
     */
    public function getForecastOfOpportunitiesData($ownerIds, $date, AclHelper $aclHelper, $start = null, $end = null)
    {
        if ($date === null) {
            return $this->getForecastOfOpportunitiesCurrentData($ownerIds, $aclHelper, $start, $end);
        }

        return $this->getForecastOfOpportunitiesOldData($ownerIds, $date, $aclHelper);
    }

    /**
     * @param array       $ownerIds
     * @param AclHelper   $aclHelper
     * @param string|null $start
     * @param string|null $end
     *
     * @return mixed
     */
    protected function getForecastOfOpportunitiesCurrentData(
        $ownerIds,
        AclHelper $aclHelper,
        $start = null,
        $end = null
    ) {
        $qb = $this->createQueryBuilder('opportunity');

        $select = "
            COUNT( opportunity.id ) as inProgressCount,
            SUM( opportunity.budgetAmount ) as budgetAmount,
            SUM( opportunity.budgetAmount * opportunity.probability ) as weightedForecast";
        $qb
            ->select($select)
            ->andWhere('opportunity.status NOT IN (:notCountedStatuses)')
            ->setParameter('notCountedStatuses', ['lost', 'won']);
        if (!empty($ownerIds)) {
            $qb->join('opportunity.owner', 'owner');
            QueryUtils::applyOptimizedIn($qb, 'owner.id', $ownerIds);
        }

        $probabilityCondition = $qb->expr()->orX(
            $qb->expr()->andX(
                'opportunity.probability <> 0',
                'opportunity.probability <> 1'
            ),
            'opportunity.probability is NULL'
        );

        $qb->andWhere($probabilityCondition);
        if ($start) {
            $qb
                ->andWhere('opportunity.closeDate >= :startDate')
                ->setParameter('startDate', $start);
        }
        if ($end) {
            $qb
                ->andWhere('opportunity.closeDate <= :endDate')
                ->setParameter('endDate', $end);
        }

        return $aclHelper->apply($qb)->getOneOrNullResult();
    }

    /**
     * @param array     $ownerIds
     * @param \DateTime $date
     * @param AclHelper $aclHelper
     *
     * @return mixed
     */
    protected function getForecastOfOpportunitiesOldData($ownerIds, $date, AclHelper $aclHelper)
    {
        //clone date for avoiding wrong date on printing with current locale
        $newDate = clone $date;
        $qb      = $this->createQueryBuilder('opportunity')
            ->where('opportunity.createdAt < :date')
            ->setParameter('date', $newDate);

        $opportunities = $aclHelper->apply($qb)->getResult();

        $result['inProgressCount']  = 0;
        $result['budgetAmount']     = 0;
        $result['weightedForecast'] = 0;

        $auditRepository = $this->getEntityManager()->getRepository('OroDataAuditBundle:Audit');
        /** @var Opportunity $opportunity */
        foreach ($opportunities as $opportunity) {
            $auditQb = $auditRepository->getLogEntriesQueryBuilder($opportunity);
            $auditQb->andWhere('a.action = :action')
                ->andWhere('a.loggedAt > :date')
                ->setParameter('action', LoggableManager::ACTION_UPDATE)
                ->setParameter('date', $newDate);
            $opportunityHistory = $aclHelper->apply($auditQb)->getResult();

            if ($oldProbability = $this->getHistoryOldValue($opportunityHistory, 'probability')) {
                $isProbabilityOk = $oldProbability !== 0 && $oldProbability !== 1;
                $probability     = $oldProbability;
            } else {
                $probability     = $opportunity->getProbability();
                $isProbabilityOk = !is_null($probability) && $probability !== 0 && $probability !== 1;
            }

            if ($isProbabilityOk
                && $this->isOwnerOk($ownerIds, $opportunityHistory, $opportunity)
                && $this->isStatusOk($opportunityHistory, $opportunity)
            ) {
                $result = $this->calculateOpportunityOldValue($result, $opportunityHistory, $opportunity, $probability);
            }
        }

        return $result;
    }

    /**
     * @param mixed  $opportunityHistory
     * @param string $field
     *
     * @return mixed
     */
    protected function getHistoryOldValue($opportunityHistory, $field)
    {
        $result = null;

        $opportunityHistory = is_array($opportunityHistory) ? $opportunityHistory : [$opportunityHistory];
        foreach ($opportunityHistory as $item) {
            if ($item->getField($field)) {
                $result = $item->getField($field)->getOldValue();
            }
        }

        return $result;
    }

    /**
     * @param array       $opportunityHistory
     * @param Opportunity $opportunity
     *
     * @return bool
     */
    protected function isStatusOk($opportunityHistory, $opportunity)
    {
        if ($oldStatus = $this->getHistoryOldValue($opportunityHistory, 'status')) {
            $isStatusOk = $oldStatus === self::OPPORTUNITY_STATE_IN_PROGRESS;
        } else {
            $isStatusOk = $opportunity->getStatus()->getName() === self::OPPORTUNITY_STATE_IN_PROGRESS_CODE;
        }

        return $isStatusOk;
    }

    /**
     * @param array       $ownerIds
     * @param array       $opportunityHistory
     * @param Opportunity $opportunity
     *
     * @return bool
     */
    protected function isOwnerOk($ownerIds, $opportunityHistory, $opportunity)
    {
        $userRepository = $this->getEntityManager()->getRepository('OroUserBundle:User');
        if ($oldOwner = $this->getHistoryOldValue($opportunityHistory, 'owner')) {
            $isOwnerOk = in_array($userRepository->findOneByUsername($oldOwner)->getId(), $ownerIds);
        } else {
            $isOwnerOk = in_array($opportunity->getOwner()->getId(), $ownerIds);
        }

        return $isOwnerOk;
    }

    /**
     * @param array       $result
     * @param array       $opportunityHistory
     * @param Opportunity $opportunity
     * @param mixed       $probability
     *
     * @return array
     */
    protected function calculateOpportunityOldValue($result, $opportunityHistory, $opportunity, $probability)
    {
        ++$result['inProgressCount'];
        $oldBudgetAmount = $this->getHistoryOldValue($opportunityHistory, 'budgetAmount');

        $budget = $oldBudgetAmount !== null ? $oldBudgetAmount : $opportunity->getBudgetAmount();
        $result['budgetAmount'] += $budget;
        $result['weightedForecast'] += $budget * $probability;

        return $result;
    }

    /**
     * @param AclHelper $aclHelper
     * @param DateTime  $start
     * @param DateTime  $end
     * @param int[]     $owners
     *
     * @return int
     */
    public function getOpportunitiesCount(
        AclHelper $aclHelper,
        DateTime $start = null,
        DateTime $end = null,
        $owners = []
    ) {
        $qb = $this->createOpportunitiesCountQb($start, $end, $owners);

        return $aclHelper->apply($qb)->getSingleScalarResult();
    }

    /**
     * @param AclHelper $aclHelper
     * @param DateTime  $start
     * @param DateTime  $end
     * @param int[]     $owners
     *
     * @return int
     */
    public function getNewOpportunitiesCount(
        AclHelper $aclHelper,
        DateTime $start = null,
        DateTime $end = null,
        $owners = []
    ) {
        $qb = $this->createOpportunitiesCountQb($start, $end, $owners)
            ->andWhere('o.closeDate IS NULL');

        return $aclHelper->apply($qb)->getSingleScalarResult();
    }

    /**
     * @param DateTime $start
     * @param DateTime $end
     * @param int[]    $owners
     *
     * @return QueryBuilder
     */
    public function createOpportunitiesCountQb(DateTime $start = null, DateTime $end = null, $owners = [])
    {
        $qb = $this->createQueryBuilder('o');
        $qb->select('COUNT(o.id)');
        if ($start) {
            $qb
                ->andWhere('o.createdAt > :start')
                ->setParameter('start', $start);
        }
        if ($end) {
            $qb
                ->andWhere('o.createdAt < :end')
                ->setParameter('end', $end);
        }

        if ($owners) {
            QueryUtils::applyOptimizedIn($qb, 'o.owner', $owners);
        }

        return $qb;
    }

    /**
     * @param AclHelper $aclHelper
     * @param DateTime  $start
     * @param DateTime  $end
     * @param int[]     $owners
     *
     * @return double
     */
    public function getTotalServicePipelineAmount(
        AclHelper $aclHelper,
        DateTime $start = null,
        DateTime $end = null,
        $owners = []
    ) {
        $qb = $this->createQueryBuilder('o');

        $qb
            ->select('SUM(o.budgetAmount)')
            ->andWhere('o.closeDate IS NULL')
            ->andWhere('o.status = :status')
            ->andWhere('o.probability != 0')
            ->andWhere('o.probability != 1')
            ->setParameter('status', self::OPPORTUNITY_STATE_IN_PROGRESS_CODE);
        if ($start) {
            $qb
                ->andWhere('o.createdAt > :start')
                ->setParameter('start', $start);
        }
        if ($end) {
            $qb
                ->andWhere('o.createdAt < :end')
                ->setParameter('end', $end);
        }

        if ($owners) {
            QueryUtils::applyOptimizedIn($qb, 'o.owner', $owners);
        }

        return $aclHelper->apply($qb)->getSingleScalarResult();
    }

    /**
     * @param AclHelper $aclHelper
     * @param DateTime  $start
     * @param DateTime  $end
     *
     * @return double
     */
    public function getTotalServicePipelineAmountInProgress(
        AclHelper $aclHelper,
        DateTime $start = null,
        DateTime $end = null
    ) {
        $qb = $this->createQueryBuilder('o');

        $qb
            ->select('SUM(o.budgetAmount)')
            ->andWhere('o.status = :status')
            ->andWhere('o.probability != 0')
            ->andWhere('o.probability != 1')
            ->setParameter('status', self::OPPORTUNITY_STATE_IN_PROGRESS_CODE);
        if ($start) {
            $qb
                ->andWhere('o.createdAt > :start')
                ->setParameter('start', $start);
        }
        if ($end) {
            $qb
                ->andWhere('o.createdAt < :end')
                ->setParameter('end', $end);
        }

        return $aclHelper->apply($qb)->getSingleScalarResult();
    }

    /**
     * @param AclHelper $aclHelper
     * @param DateTime  $start
     * @param DateTime  $end
     *
     * @return double
     */
    public function getWeightedPipelineAmount(AclHelper $aclHelper, DateTime $start = null, DateTime $end = null)
    {
        $qb = $this->createQueryBuilder('o');

        $qb->select('SUM(o.budgetAmount * o.probability)');
        if ($start) {
            $qb
                ->andWhere('o.createdAt > :start')
                ->setParameter('start', $start);
        }
        if ($end) {
            $qb
                ->andWhere('o.createdAt < :end')
                ->setParameter('end', $end);
        }

        return $aclHelper->apply($qb)->getSingleScalarResult();
    }

    /**
     * @param AclHelper $aclHelper
     * @param DateTime  $start
     * @param DateTime  $end
     * @param int[]     $owners
     *
     * @return double
     */
    public function getOpenWeightedPipelineAmount(
        AclHelper $aclHelper,
        DateTime $start = null,
        DateTime $end = null,
        $owners = []
    ) {
        $qb = $this->createQueryBuilder('o');

        $qb
            ->select('SUM(o.budgetAmount * o.probability)')
            ->andWhere('o.status = :status')
            ->andWhere('o.probability != 0')
            ->andWhere('o.probability != 1')
            ->setParameter('status', self::OPPORTUNITY_STATE_IN_PROGRESS_CODE);
        if ($start) {
            $qb
                ->andWhere('o.createdAt > :start')
                ->setParameter('start', $start);
        }
        if ($end) {
            $qb
                ->andWhere('o.createdAt < :end')
                ->setParameter('end', $end);
        }

        if ($owners) {
            QueryUtils::applyOptimizedIn($qb, 'o.owner', $owners);
        }

        return $aclHelper->apply($qb)->getSingleScalarResult();
    }
}
