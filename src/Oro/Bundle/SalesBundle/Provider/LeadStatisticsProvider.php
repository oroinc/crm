<?php

namespace Oro\Bundle\SalesBundle\Provider;

use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\SalesBundle\Entity\Repository\LeadRepository;

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
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroSalesBundle:Lead', 'createdAt');

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
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroSalesBundle:Lead', 'createdAt');

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
            $this->leadRepository = $this->doctrine->getRepository('OroSalesBundle:Lead');
        }

        return $this->leadRepository;
    }
}
