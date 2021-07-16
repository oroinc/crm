<?php

namespace Oro\Bundle\SalesBundle\Entity\Repository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\DashboardBundle\Filter\DateFilterProcessor;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Component\DoctrineUtils\ORM\QueryBuilderUtil;

/**
 * Doctrine repository for Opportunity entity
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class OpportunityRepository extends EntityRepository
{
    const OPPORTUNITY_STATE_IN_PROGRESS      = 'In Progress';
    const OPPORTUNITY_STATE_IN_PROGRESS_CODE = 'in_progress';
    const OPPORTUNITY_STATUS_CLOSED_WON_CODE = 'won';

    /**
     * @param array             $removingCurrencies
     * @param Organization|null $organization
     *
     * @return bool
     */
    public function hasRecordsWithRemovingCurrencies(
        array $removingCurrencies,
        Organization $organization = null
    ) {
        $qb = $this->createQueryBuilder('opportunity');
        $qb
            ->select('COUNT(opportunity.id)')
            ->where($qb->expr()->in('opportunity.budgetAmountCurrency', ':removingCurrencies'))
            ->orWhere($qb->expr()->in('opportunity.closeRevenueCurrency', ':removingCurrencies'))
            ->setParameter('removingCurrencies', $removingCurrencies);
        if ($organization instanceof Organization) {
            $qb->andWhere('opportunity.organization = :organization');
            $qb->setParameter(':organization', $organization);
        }

        return (bool) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param \DateTime  $start
     * @param \DateTime  $end
     *
     * @return QueryBuilder
     */
    public function getOpportunitiesCountQB(
        \DateTime $start = null,
        \DateTime $end = null
    ) {
        $qb = $this->createQueryBuilder('o');
        $qb->select('COUNT(o.id)');
        if ($start) {
            $qb
                ->andWhere('o.createdAt >= :start')
                ->setParameter('start', $start, Types::DATETIME_MUTABLE);
        }
        if ($end) {
            $qb
                ->andWhere('o.createdAt <= :end')
                ->setParameter('end', $end, Types::DATETIME_MUTABLE);
        }

        return $qb;
    }

    /**
     * @param \DateTime|null $start
     * @param \DateTime|null $end
     *
     * @return QueryBuilder
     */
    public function getOpportunitiesByPeriodQB(\DateTime $start = null, \DateTime $end = null)
    {
        $qb = $this->createQueryBuilder('o');
        $this->setCreationPeriod($qb, $start, $end);

        return $qb;
    }

    /**
     * @param \DateTime|null $start
     * @param \DateTime|null $end
     *
     * @return QueryBuilder
     */
    public function getWonOpportunitiesCountByPeriodQB(\DateTime $start = null, \DateTime $end = null)
    {
        $qb = $this->createQueryBuilder('o');
        $qb->select('COUNT(o.id)')
            ->andWhere('o.status = :status')
            ->setParameter('status', self::OPPORTUNITY_STATUS_CLOSED_WON_CODE);
        $this->setClosedPeriod($qb, $start, $end);

        return $qb;
    }

    /**
     * @param \DateTime|null $start
     * @param \DateTime|null $end
     *
     * @return QueryBuilder
     */
    public function getWonOpportunitiesByPeriodQB(\DateTime $start = null, \DateTime $end = null)
    {
        $qb = $this->createQueryBuilder('o')
            ->andWhere('o.status = :status')
            ->setParameter('status', self::OPPORTUNITY_STATUS_CLOSED_WON_CODE);

        $this->setClosedPeriod($qb, $start, $end);

        return $qb;
    }

    protected function setCreationPeriod(QueryBuilder $qb, \DateTime $start = null, \DateTime $end = null)
    {
        if ($start) {
            $qb->andWhere('o.createdAt >= :dateStart')->setParameter('dateStart', $start, Types::DATETIME_MUTABLE);
        }

        if ($end) {
            $qb->andWhere('o.createdAt <= :dateEnd')->setParameter('dateEnd', $end, Types::DATETIME_MUTABLE);
        }
    }

    protected function setClosedPeriod(QueryBuilder $qb, \DateTime $start = null, \DateTime $end = null)
    {
        if ($start) {
            $qb->andWhere('o.closedAt >= :dateStart')->setParameter('dateStart', $start, Types::DATETIME_MUTABLE);
        }

        if ($end) {
            $qb->andWhere('o.closedAt <= :dateEnd')->setParameter('dateEnd', $end, Types::DATETIME_MUTABLE);
        }
    }

    /**
     * Returns count of opportunities grouped by lead source
     *
     * @param AclHelper $aclHelper
     * @param DateFilterProcessor $dateFilterProcessor
     * @param array $dateRange
     * @param array $owners
     *
     * @return array [value, source]
     */
    public function getOpportunitiesCountGroupByLeadSource(
        AclHelper $aclHelper,
        DateFilterProcessor $dateFilterProcessor,
        array $dateRange = [],
        array $owners = []
    ) {
        $qb = $this->getOpportunitiesGroupByLeadSourceQueryBuilder($dateFilterProcessor, $dateRange, $owners);
        $qb->addSelect('count(o.id) as value');

        return $aclHelper->apply($qb)->getArrayResult();
    }

    /**
     * Returns opportunities QB grouped by lead source filtered by $dateRange and $owners
     *
     * @param DateFilterProcessor $dateFilterProcessor
     * @param array $dateRange
     * @param array $owners
     *
     * @return QueryBuilder
     */
    public function getOpportunitiesGroupByLeadSourceQueryBuilder(
        DateFilterProcessor $dateFilterProcessor,
        array $dateRange = [],
        array $owners = []
    ) {
        $qb = $this->createQueryBuilder('o')
            ->select('s.id as source')
            ->leftJoin('o.lead', 'l')
            ->leftJoin('l.source', 's')
            ->groupBy('source');

        $dateFilterProcessor->process($qb, $dateRange, 'o.createdAt');

        if ($owners) {
            QueryBuilderUtil::applyOptimizedIn($qb, 'o.owner', $owners);
        }

        return $qb;
    }

    /**
     * @param string $alias
     * @param CurrencyQueryBuilderTransformerInterface $qbTransformer
     * @param array $excludedStatuses
     *
     * @return QueryBuilder
     */
    public function getForecastQB(
        CurrencyQueryBuilderTransformerInterface $qbTransformer,
        $alias = 'o',
        array $excludedStatuses = ['lost', 'won']
    ) {
        QueryBuilderUtil::checkIdentifier($alias);
        $qb = $this->createQueryBuilder($alias);
        $baBaseCurrencyQuery = $qbTransformer->getTransformSelectQuery('budgetAmount', $qb, $alias);
        $qb->select(
            QueryBuilderUtil::sprintf('COUNT(%s.id) as inProgressCount', $alias),
            sprintf('SUM(%s) as budgetAmount', $baBaseCurrencyQuery),
            sprintf('SUM((%s) * %s.probability) as weightedForecast', $baBaseCurrencyQuery, $alias)
        );

        if ($excludedStatuses) {
            $qb->andWhere($qb->expr()->notIn(QueryBuilderUtil::getField($alias, 'status'), $excludedStatuses));
        }

        return $qb;
    }
}
