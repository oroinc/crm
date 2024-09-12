<?php

namespace Oro\Bundle\SalesBundle\Provider\Opportunity;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\DashboardBundle\Filter\WidgetProviderFilterManager;
use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\DashboardBundle\Provider\BigNumber\BigNumberDateHelper;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Entity\Repository\OpportunityRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

/**
 * The provider for opportunity statistics to use in widgets.
 */
class OpportunityStatisticsProvider
{
    private ManagerRegistry $doctrine;
    private AclHelper $aclHelper;
    private WidgetProviderFilterManager $widgetProviderFilter;
    private BigNumberDateHelper $dateHelper;
    private CurrencyQueryBuilderTransformerInterface $qbTransformer;

    public function __construct(
        ManagerRegistry $doctrine,
        AclHelper $aclHelper,
        WidgetProviderFilterManager $widgetProviderFilter,
        BigNumberDateHelper $dateHelper,
        CurrencyQueryBuilderTransformerInterface $qbTransformer
    ) {
        $this->doctrine = $doctrine;
        $this->aclHelper = $aclHelper;
        $this->widgetProviderFilter = $widgetProviderFilter;
        $this->dateHelper = $dateHelper;
        $this->qbTransformer = $qbTransformer;
    }

    public function getNewOpportunitiesCount(array $dateRange, WidgetOptionBag $widgetOptions): int
    {
        [$start, $end] = $this->dateHelper->getPeriod($dateRange, Opportunity::class, 'createdAt');
        $qb = $this->getOpportunityRepository()->getOpportunitiesCountQB($start, $end);

        return $this->processDataQueryBuilder($qb, $widgetOptions)->getSingleScalarResult();
    }

    public function getOpportunitiesCount(array $dateRange, WidgetOptionBag $widgetOptions): int
    {
        [$start, $end] = $this->dateHelper->getPeriod($dateRange, Opportunity::class, 'createdAt');
        $qb = $this->getOpportunityRepository()->getOpportunitiesCountQB($start, $end);

        return $this->processDataQueryBuilder($qb, $widgetOptions)->getSingleScalarResult();
    }

    public function getNewOpportunitiesAmount(array $dateRange, WidgetOptionBag $widgetOptions): float
    {
        [$start, $end] = $this->dateHelper->getPeriod($dateRange, Opportunity::class, 'createdAt');
        $qb = $this->getOpportunityRepository()->getOpportunitiesByPeriodQB($start, $end);
        $qb->select(sprintf('SUM(%s)', $this->qbTransformer->getTransformSelectQuery('budgetAmount', $qb)));

        return $this->processDataQueryBuilder($qb, $widgetOptions)->getSingleScalarResult() ?? 0.0;
    }

    public function getWonOpportunitiesToDateCount(array $dateRange, WidgetOptionBag $widgetOptions): int
    {
        [$start, $end] = $this->dateHelper->getPeriod($dateRange, Opportunity::class, 'createdAt');
        $qb = $this->getOpportunityRepository()->getWonOpportunitiesCountByPeriodQB($start, $end);

        return $this->processDataQueryBuilder($qb, $widgetOptions)->getSingleScalarResult();
    }

    public function getWonOpportunitiesToDateAmount(array $dateRange, WidgetOptionBag $widgetOptions): float
    {
        [$start, $end] = $this->dateHelper->getPeriod($dateRange, Opportunity::class, 'createdAt');
        $qb = $this->getOpportunityRepository()->getWonOpportunitiesByPeriodQB($start, $end);
        $qb->select(sprintf('SUM(%s)', $this->qbTransformer->getTransformSelectQuery('closeRevenue', $qb)));

        return $this->processDataQueryBuilder($qb, $widgetOptions)->getSingleScalarResult() ?? 0.0;
    }

    private function getOpportunityRepository(): OpportunityRepository
    {
        return $this->doctrine->getRepository(Opportunity::class);
    }

    private function processDataQueryBuilder(QueryBuilder $qb, WidgetOptionBag $widgetOptions): Query
    {
        $this->widgetProviderFilter->filter($qb, $widgetOptions);

        return $this->aclHelper->apply($qb);
    }
}
