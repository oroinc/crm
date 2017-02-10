<?php

namespace Oro\Bundle\SalesBundle\Provider;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\DashboardBundle\Provider\BigNumber\BigNumberDateHelper;
use Oro\Bundle\DashboardBundle\Filter\WidgetProviderFilter;
use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;

class B2bBigNumberProvider
{
    /** @var RegistryInterface */
    protected $doctrine;

    /** @var WidgetProviderFilter */
    protected $widgetProviderFilter;

    /** @var BigNumberDateHelper */
    protected $dateHelper;

    /** @var CurrencyQueryBuilderTransformerInterface  */
    protected $qbTransformer;

    /**
     * @param RegistryInterface $doctrine
     * @param WidgetProviderFilter $widgetProviderFilter
     * @param BigNumberDateHelper $dateHelper
     * @param CurrencyQueryBuilderTransformerInterface $qbTransformer
     */
    public function __construct(
        RegistryInterface $doctrine,
        WidgetProviderFilter $widgetProviderFilter,
        BigNumberDateHelper $dateHelper,
        CurrencyQueryBuilderTransformerInterface $qbTransformer
    ) {
        $this->doctrine             = $doctrine;
        $this->widgetProviderFilter = $widgetProviderFilter;
        $this->dateHelper           = $dateHelper;
        $this->qbTransformer        = $qbTransformer;
    }

    /**
     * @param array $dateRange
     * @param WidgetOptionBag $widgetOptions
     *
     * @return int
     */
    public function getNewLeadsCount($dateRange, WidgetOptionBag $widgetOptions)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroSalesBundle:Lead', 'createdAt');

        return $this->doctrine
            ->getRepository('OroSalesBundle:Lead')
            ->getNewLeadsCount($this->widgetProviderFilter, $start, $end, $widgetOptions);
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

        return $this->doctrine
            ->getRepository('OroSalesBundle:Lead')
            ->getLeadsCount($this->widgetProviderFilter, $start, $end, $widgetOptions);
    }

    /**
     * @param array $dateRange
     * @param WidgetOptionBag $widgetOptions
     *
     * @return int
     */
    public function getOpenLeadsCount($dateRange, WidgetOptionBag $widgetOptions)
    {
        return $this->doctrine
            ->getRepository('OroSalesBundle:Lead')
            ->getOpenLeadsCount($this->widgetProviderFilter, $widgetOptions);
    }

    /**
     * @param array $dateRange
     * @param WidgetOptionBag $widgetOptions
     *
     * @return int
     */
    public function getNewOpportunitiesCount($dateRange, WidgetOptionBag $widgetOptions)
    {
        list ($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroSalesBundle:Opportunity', 'createdAt');

        return $this->doctrine
            ->getRepository('OroSalesBundle:Opportunity')
            ->getNewOpportunitiesCount($this->widgetProviderFilter, $start, $end, $widgetOptions);
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

        return $this->doctrine
            ->getRepository('OroSalesBundle:Opportunity')
            ->getOpportunitiesCount($this->widgetProviderFilter, $start, $end, $widgetOptions);
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
                $this->qbTransformer,$start,
                $end,
                $widgetOptions
            );
    }
}
