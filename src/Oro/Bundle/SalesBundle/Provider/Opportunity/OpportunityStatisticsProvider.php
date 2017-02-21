<?php

namespace Oro\Bundle\SalesBundle\Provider\Opportunity;

use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\SalesBundle\Provider\B2bBigNumberProvider;
use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;

class OpportunityStatisticsProvider extends B2bBigNumberProvider
{
    /** @var CurrencyQueryBuilderTransformerInterface */
    protected $qbTransformer;

    /**
     * @param array $dateRange
     * @param WidgetOptionBag $widgetOptions
     *
     * @return int
     */
    public function getNewOpportunitiesCount($dateRange, WidgetOptionBag $widgetOptions)
    {
        list ($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroSalesBundle:Opportunity', 'createdAt');

        $qb = $this->doctrine->getRepository('OroSalesBundle:Opportunity')->getNewOpportunitiesCountQB($start, $end);

        return $this->widgetProviderFilter->filter($qb, $widgetOptions)->getSingleScalarResult();
    }

    /**
     * @param array $dateRange
     * @param WidgetOptionBag $widgetOptions
     *
     * @return int
     */
    public function getOpportunitiesCount(array $dateRange, WidgetOptionBag $widgetOptions)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroSalesBundle:Opportunity', 'createdAt');

        $qb = $this->doctrine->getRepository('OroSalesBundle:Opportunity')->getOpportunitiesCountQB($start, $end);

        return $this->widgetProviderFilter->filter($qb, $widgetOptions)->getSingleScalarResult();
    }

    /**
     * @param array $dateRange
     * @param WidgetOptionBag $widgetOptions
     *
     * @return double
     */
    public function getNewOpportunitiesAmount($dateRange, WidgetOptionBag $widgetOptions)
    {
        list ($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroSalesBundle:Opportunity', 'createdAt');

        return $this->doctrine
            ->getRepository('OroSalesBundle:Opportunity')
            ->getNewOpportunitiesAmount(
                $this->widgetProviderFilter,
                $this->qbTransformer,
                $start,
                $end,
                $widgetOptions
            );
    }

    /**
     * @param array $dateRange
     * @param WidgetOptionBag $widgetOptions
     *
     * @return int
     */
    public function getWonOpportunitiesToDateCount($dateRange, WidgetOptionBag $widgetOptions)
    {
        list ($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroSalesBundle:Opportunity', 'createdAt');

        return $this->doctrine
            ->getRepository('OroSalesBundle:Opportunity')
            ->getWonOpportunitiesToDateCount($this->widgetProviderFilter, $start, $end, $widgetOptions);
    }

    /**
     * @param array $dateRange
     * @param WidgetOptionBag $widgetOptions
     *
     * @return double
     */
    public function getWonOpportunitiesToDateAmount($dateRange, WidgetOptionBag $widgetOptions)
    {
        list ($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroSalesBundle:Opportunity', 'createdAt');

        return $this->doctrine
            ->getRepository('OroSalesBundle:Opportunity')
            ->getWonOpportunitiesToDateAmount(
                $this->widgetProviderFilter,
                $this->qbTransformer,
                $start,
                $end,
                $widgetOptions
            );
    }

    /**
     * @param CurrencyQueryBuilderTransformerInterface $qbTransformer
     */
    public function setCurrencyQBTransformer(CurrencyQueryBuilderTransformerInterface $qbTransformer)
    {
        $this->qbTransformer = $qbTransformer;
    }
}
