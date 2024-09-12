<?php

namespace Oro\Bundle\SalesBundle\Provider;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\DashboardBundle\Filter\WidgetProviderFilterManager;
use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\DashboardBundle\Provider\BigNumber\BigNumberDateHelper;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Repository\LeadRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

/**
 * The provider for lead statistics to use in widgets.
 */
class LeadStatisticsProvider
{
    private ManagerRegistry $doctrine;
    private AclHelper $aclHelper;
    private WidgetProviderFilterManager $widgetProviderFilter;
    private BigNumberDateHelper $dateHelper;

    public function __construct(
        ManagerRegistry $doctrine,
        AclHelper $aclHelper,
        WidgetProviderFilterManager $widgetProviderFilter,
        BigNumberDateHelper $dateHelper
    ) {
        $this->doctrine = $doctrine;
        $this->aclHelper = $aclHelper;
        $this->widgetProviderFilter = $widgetProviderFilter;
        $this->dateHelper = $dateHelper;
    }

    public function getNewLeadsCount(array $dateRange, WidgetOptionBag $widgetOptions): int
    {
        [$start, $end] = $this->dateHelper->getPeriod($dateRange, Lead::class, 'createdAt');
        $qb = $this->getLeadRepository()->getNewLeadsCountQB($start, $end);

        return $this->processDataQueryBuilder($qb, $widgetOptions)->getSingleScalarResult();
    }

    public function getLeadsCount(array $dateRange, WidgetOptionBag $widgetOptions): int
    {
        [$start, $end] = $this->dateHelper->getPeriod($dateRange, Lead::class, 'createdAt');
        $qb = $this->getLeadRepository()->getLeadsCountQB($start, $end);

        return $this->processDataQueryBuilder($qb, $widgetOptions)->getSingleScalarResult();
    }

    public function getOpenLeadsCount(array $dateRange, WidgetOptionBag $widgetOptions): int
    {
        $qb = $this->getLeadRepository()->getOpenLeadsCountQB();

        return $this->processDataQueryBuilder($qb, $widgetOptions)->getSingleScalarResult();
    }

    private function getLeadRepository(): LeadRepository
    {
        return $this->doctrine->getRepository(Lead::class);
    }

    private function processDataQueryBuilder(QueryBuilder $qb, WidgetOptionBag $widgetOptions): Query
    {
        $this->widgetProviderFilter->filter($qb, $widgetOptions);

        return $this->aclHelper->apply($qb);
    }
}
