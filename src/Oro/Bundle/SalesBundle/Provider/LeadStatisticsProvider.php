<?php

namespace Oro\Bundle\SalesBundle\Provider;

use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Repository\LeadRepository;

/**
 * The provider for lead statistics to use in widgets.
 */
class LeadStatisticsProvider extends B2bBigNumberProvider
{
    /** @var LeadRepository  */
    protected $leadRepository;

    /**
     * @param array $dateRange
     * @param WidgetOptionBag $widgetOptions
     *
     * @return int
     */
    public function getNewLeadsCount($dateRange, WidgetOptionBag $widgetOptions)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, Lead::class, 'createdAt');

        $queryBuilder = $this->getLeadRepository()->getNewLeadsCountQB($start, $end);

        return $this->processDataQueryBuilder($queryBuilder, $widgetOptions)->getSingleScalarResult();
    }

    /**
     * @param array $dateRange
     * @param WidgetOptionBag $widgetOptions
     *
     * @return int
     */
    public function getLeadsCount($dateRange, WidgetOptionBag $widgetOptions)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, Lead::class, 'createdAt');

        $queryBuilder = $this->getLeadRepository()->getLeadsCountQB($start, $end);

        return $this->processDataQueryBuilder($queryBuilder, $widgetOptions)->getSingleScalarResult();
    }

    /**
     * @param array $dateRange
     * @param WidgetOptionBag $widgetOptions
     *
     * @return int
     */
    public function getOpenLeadsCount($dateRange, WidgetOptionBag $widgetOptions)
    {
        $queryBuilder = $this->getLeadRepository()->getOpenLeadsCountQB();

        return $this->processDataQueryBuilder($queryBuilder, $widgetOptions)->getSingleScalarResult();
    }

    /**
     * @return LeadRepository
     */
    protected function getLeadRepository()
    {
        if (null === $this->leadRepository) {
            $this->leadRepository = $this->doctrine->getRepository(Lead::class);
        }

        return $this->leadRepository;
    }
}
