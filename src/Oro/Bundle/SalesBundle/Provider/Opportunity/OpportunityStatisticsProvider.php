<?php

namespace Oro\Bundle\SalesBundle\Provider\Opportunity;

use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\SalesBundle\Entity\Repository\OpportunityRepository;
use Oro\Bundle\SalesBundle\Provider\B2bBigNumberProvider;

/**
 * Provides methods to get statistics of opportunities
 */
class OpportunityStatisticsProvider extends B2bBigNumberProvider
{
    /** @var CurrencyQueryBuilderTransformerInterface */
    protected $qbTransformer;

    /** @var OpportunityRepository */
    protected $opportunityRepository;

    /**
     * @param array $dateRange
     * @param WidgetOptionBag $widgetOptions
     *
     * @return int
     */
    public function getNewOpportunitiesCount($dateRange, WidgetOptionBag $widgetOptions)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroSalesBundle:Opportunity', 'createdAt');

        $queryBuilder = $this->getOpportunityRepository()->getOpportunitiesCountQB($start, $end);

        return $this->processDataQueryBuilder($queryBuilder, $widgetOptions)->getSingleScalarResult();
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

        $queryBuilder = $this->getOpportunityRepository()->getOpportunitiesCountQB($start, $end);

        return $this->processDataQueryBuilder($queryBuilder, $widgetOptions)->getSingleScalarResult();
    }

    /**
     * @param array $dateRange
     * @param WidgetOptionBag $widgetOptions
     *
     * @return double
     */
    public function getNewOpportunitiesAmount($dateRange, WidgetOptionBag $widgetOptions)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroSalesBundle:Opportunity', 'createdAt');

        $queryBuilder = $this->getOpportunityRepository()->getOpportunitiesByPeriodQB($start, $end);
        $select = sprintf('SUM(%s)', $this->qbTransformer->getTransformSelectQuery('budgetAmount', $queryBuilder));
        $queryBuilder->select($select);

        return $this->processDataQueryBuilder($queryBuilder, $widgetOptions)->getSingleScalarResult();
    }

    /**
     * @param array $dateRange
     * @param WidgetOptionBag $widgetOptions
     *
     * @return int
     */
    public function getWonOpportunitiesToDateCount($dateRange, WidgetOptionBag $widgetOptions)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroSalesBundle:Opportunity', 'createdAt');
        $queryBuilder = $this->getOpportunityRepository()->getWonOpportunitiesCountByPeriodQB($start, $end);

        return $this->processDataQueryBuilder($queryBuilder, $widgetOptions)->getSingleScalarResult();
    }

    /**
     * @param array $dateRange
     * @param WidgetOptionBag $widgetOptions
     *
     * @return double
     */
    public function getWonOpportunitiesToDateAmount($dateRange, WidgetOptionBag $widgetOptions)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroSalesBundle:Opportunity', 'createdAt');

        $queryBuilder = $this->getOpportunityRepository()->getWonOpportunitiesByPeriodQB($start, $end);
        $select = sprintf('SUM(%s)', $this->qbTransformer->getTransformSelectQuery('closeRevenue', $queryBuilder));
        $queryBuilder->select($select);

        return $this->processDataQueryBuilder($queryBuilder, $widgetOptions)->getSingleScalarResult();
    }

    public function setCurrencyQBTransformer(CurrencyQueryBuilderTransformerInterface $qbTransformer)
    {
        $this->qbTransformer = $qbTransformer;
    }

    /**
     * @return OpportunityRepository
     */
    protected function getOpportunityRepository()
    {
        if (null === $this->opportunityRepository) {
            $this->opportunityRepository = $this->doctrine->getRepository('OroSalesBundle:Opportunity');
        }

        return $this->opportunityRepository;
    }
}
