<?php

namespace Oro\Bundle\SalesBundle\Provider;

use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;

class LeadStatisticsProvider extends B2bBigNumberProvider
{
    /**
     * @param array $dateRange
     * @param WidgetOptionBag $widgetOptions
     *
     * @return int
     */
    public function getNewLeadsCount($dateRange, WidgetOptionBag $widgetOptions)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroSalesBundle:Lead', 'createdAt');

        $qb = $this->doctrine->getRepository('OroSalesBundle:Lead')->getNewLeadsCountQB($start, $end);

        return $this->widgetProviderFilter->filter($qb, $widgetOptions)->getSingleScalarResult();
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

        $qb =  $this->doctrine->getRepository('OroSalesBundle:Lead')->getLeadsCountQB($start, $end);

        return $this->widgetProviderFilter->filter($qb, $widgetOptions)->getSingleScalarResult();
    }

    /**
     * @param array $dateRange
     * @param WidgetOptionBag $widgetOptions
     *
     * @return int
     */
    public function getOpenLeadsCount($dateRange, WidgetOptionBag $widgetOptions)
    {
        $qb = $this->doctrine->getRepository('OroSalesBundle:Lead')->getOpenLeadsCountQB();

        return $this->widgetProviderFilter->filter($qb, $widgetOptions)->getSingleScalarResult();
    }
}
