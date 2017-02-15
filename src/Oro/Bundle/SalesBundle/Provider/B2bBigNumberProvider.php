<?php

namespace Oro\Bundle\SalesBundle\Provider;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\DashboardBundle\Provider\BigNumber\BigNumberDateHelper;
use Oro\Bundle\DashboardBundle\Filter\WidgetProviderFilterManager;
use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;

class B2bBigNumberProvider
{
    /** @var RegistryInterface */
    protected $doctrine;

    /** @var WidgetProviderFilterManager */
    protected $widgetProviderFilter;

    /** @var BigNumberDateHelper */
    protected $dateHelper;

    /** @var CurrencyQueryBuilderTransformerInterface  */
    protected $qbTransformer;

    /**
     * @param RegistryInterface $doctrine
     * @param WidgetProviderFilterManager $widgetProviderFilter
     * @param BigNumberDateHelper $dateHelper
     * @param CurrencyQueryBuilderTransformerInterface $qbTransformer
     */
    public function __construct(
        RegistryInterface $doctrine,
        WidgetProviderFilterManager $widgetProviderFilter,
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

        return $this->doctrine->getRepository('OroSalesBundle:Opportunity')->getOpportunitiesCountQB($start, $end);

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
}
