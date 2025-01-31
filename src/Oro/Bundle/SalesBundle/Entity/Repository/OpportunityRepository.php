<?php

namespace Oro\Bundle\SalesBundle\Entity\Repository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\DashboardBundle\Filter\DateFilterProcessor;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOption;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Component\DoctrineUtils\ORM\QueryBuilderUtil;

/**
 * Doctrine repository for Opportunity entity.
 */
class OpportunityRepository extends EntityRepository
{
    public function hasRecordsWithRemovingCurrencies(
        array $removingCurrencies,
        ?Organization $organization = null
    ): bool {
        $qb = $this->createQueryBuilder('opportunity');
        $qb->select('COUNT(opportunity.id)')
            ->where($qb->expr()->in('opportunity.budgetAmountCurrency', ':removingCurrencies'))
            ->orWhere($qb->expr()->in('opportunity.closeRevenueCurrency', ':removingCurrencies'))
            ->setParameter('removingCurrencies', $removingCurrencies);
        if ($organization instanceof Organization) {
            $qb->andWhere('opportunity.organization = :organization');
            $qb->setParameter(':organization', $organization);
        }

        return (bool)$qb->getQuery()->getSingleScalarResult();
    }

    public function getOpportunitiesCountQB(
        ?\DateTime $start = null,
        ?\DateTime $end = null
    ): QueryBuilder {
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

    public function getOpportunitiesByPeriodQB(?\DateTime $start = null, ?\DateTime $end = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('o');
        if ($start) {
            $qb
                ->andWhere('o.createdAt >= :dateStart')
                ->setParameter('dateStart', $start, Types::DATETIME_MUTABLE);
        }
        if ($end) {
            $qb
                ->andWhere('o.createdAt <= :dateEnd')
                ->setParameter('dateEnd', $end, Types::DATETIME_MUTABLE);
        }

        return $qb;
    }

    public function getWonOpportunitiesCountByPeriodQB(?\DateTime $start = null, ?\DateTime $end = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('o');
        $qb->select('COUNT(o.id)')
            ->andWhere("JSON_EXTRACT(o.serialized_data, 'status') = :oppStatus")
            ->setParameter(
                'oppStatus',
                ExtendHelper::buildEnumOptionId(Opportunity::INTERNAL_STATUS_CODE, Opportunity::STATUS_WON)
            );
        if ($start) {
            $qb
                ->andWhere('o.closedAt >= :dateStart')
                ->setParameter('dateStart', $start, Types::DATETIME_MUTABLE);
        }
        if ($end) {
            $qb
                ->andWhere('o.closedAt <= :dateEnd')
                ->setParameter('dateEnd', $end, Types::DATETIME_MUTABLE);
        }

        return $qb;
    }

    public function getWonOpportunitiesByPeriodQB(?\DateTime $start = null, ?\DateTime $end = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('o')
            ->andWhere("JSON_EXTRACT(o.serialized_data, 'status') = :status")
            ->setParameter(
                'status',
                ExtendHelper::buildEnumOptionId(
                    Opportunity::INTERNAL_STATUS_CODE,
                    Opportunity::STATUS_WON
                )
            );
        if ($start) {
            $qb
                ->andWhere('o.closedAt >= :dateStart')
                ->setParameter('dateStart', $start, Types::DATETIME_MUTABLE);
        }
        if ($end) {
            $qb
                ->andWhere('o.closedAt <= :dateEnd')
                ->setParameter('dateEnd', $end, Types::DATETIME_MUTABLE);
        }

        return $qb;
    }

    public function getOpportunitiesCountGroupByLeadSource(
        AclHelper $aclHelper,
        DateFilterProcessor $dateFilterProcessor,
        array $dateRange = [],
        array $owners = []
    ): array {
        $qb = $this->getOpportunitiesGroupByLeadSourceQueryBuilder($dateFilterProcessor, $dateRange, $owners);
        $qb->addSelect('count(o.id) as value');

        return $aclHelper->apply($qb)->getArrayResult();
    }

    public function getOpportunitiesGroupByLeadSourceQueryBuilder(
        DateFilterProcessor $dateFilterProcessor,
        array $dateRange = [],
        array $owners = []
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('o')
            ->select('s.id as source')
            ->leftJoin('o.lead', 'l')
            ->leftJoin(
                EnumOption::class,
                's',
                Expr\Join::WITH,
                "JSON_EXTRACT(l.serialized_data, 'source') = s"
            )
            ->groupBy('source');

        $dateFilterProcessor->process($qb, $dateRange, 'o.createdAt');

        if ($owners) {
            QueryBuilderUtil::applyOptimizedIn($qb, 'o.owner', $owners);
        }

        return $qb;
    }

    public function getForecastQB(
        CurrencyQueryBuilderTransformerInterface $qbTransformer,
        string $alias = 'o',
        array $excludedStatuses = ['lost', 'won']
    ): QueryBuilder {
        QueryBuilderUtil::checkIdentifier($alias);
        $qb = $this->createQueryBuilder($alias);
        $baBaseCurrencyQuery = $qbTransformer->getTransformSelectQuery('budgetAmount', $qb, $alias);
        $qb->select(
            QueryBuilderUtil::sprintf('COUNT(%s.id) as inProgressCount', $alias),
            sprintf('SUM(%s) as budgetAmount', $baBaseCurrencyQuery),
            sprintf('SUM((%s) * %s.probability) as weightedForecast', $baBaseCurrencyQuery, $alias)
        );

        if ($excludedStatuses) {
            $qb->andWhere($qb->expr()->notIn("JSON_EXTRACT(o.serialized_data, 'status')", ':excludedStatuses'));
            $qb->setParameter(
                'excludedStatuses',
                ExtendHelper::mapToEnumOptionIds(Opportunity::INTERNAL_STATUS_CODE, $excludedStatuses)
            );
        }

        return $qb;
    }
}
