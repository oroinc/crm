<?php

namespace Oro\Bundle\SalesBundle\Provider;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\DashboardBundle\Provider\BigNumber\BigNumberDateHelper;
use Oro\Bundle\DashboardBundle\Filter\WidgetProviderFilterManager;
use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\SalesBundle\Entity\Repository\LeadRepository;
use Oro\Bundle\SalesBundle\Entity\Repository\OpportunityRepository;

class B2bBigNumberProvider
{
    /** @var RegistryInterface */
    protected $doctrine;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var WidgetProviderFilterManager */
    protected $widgetProviderFilter;

    /** @var BigNumberDateHelper */
    protected $dateHelper;

    /** @var CurrencyQueryBuilderTransformerInterface  */
    protected $qbTransformer;

    /** @var LeadRepository  */
    protected $leadRepository;

    /** @var OpportunityRepository  */
    protected $opportunityRepository;

    /**
     * @param RegistryInterface $doctrine
     * @param AclHelper $aclHelper
     * @param WidgetProviderFilterManager $widgetProviderFilter
     * @param BigNumberDateHelper $dateHelper
     * @param CurrencyQueryBuilderTransformerInterface $qbTransformer
     */
    public function __construct(
        RegistryInterface $doctrine,
        AclHelper $aclHelper,
        WidgetProviderFilterManager $widgetProviderFilter,
        BigNumberDateHelper $dateHelper,
        CurrencyQueryBuilderTransformerInterface $qbTransformer
    ) {
        $this->doctrine             = $doctrine;
        $this->aclHelper            = $aclHelper;
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
     * @param array $dateRange
     * @param WidgetOptionBag $widgetOptions
     *
     * @return int
     */
    public function getNewOpportunitiesCount($dateRange, WidgetOptionBag $widgetOptions)
    {
        list ($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroSalesBundle:Opportunity', 'createdAt');

        $queryBuilder = $this->getOpportunityRepository()->getNewOpportunitiesCountQB($start, $end);

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
        list ($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroSalesBundle:Opportunity', 'createdAt');

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
        list ($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroSalesBundle:Opportunity', 'createdAt');
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
        list ($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroSalesBundle:Opportunity', 'createdAt');

        $queryBuilder = $this->getOpportunityRepository()->getWonOpportunitiesByPeriodQB($start, $end);
        $select = sprintf('SUM(%s)', $this->qbTransformer->getTransformSelectQuery('closeRevenue', $queryBuilder));
        $queryBuilder->select($select);

        return $this->processDataQueryBuilder($queryBuilder, $widgetOptions)->getSingleScalarResult();
    }

    /**
     * Processes data and ACL filters
     *
     * @param QueryBuilder    $queryBuilder
     * @param WidgetOptionBag $widgetOptions
     *
     * @return Query
     */
    protected function processDataQueryBuilder(QueryBuilder $queryBuilder, WidgetOptionBag $widgetOptions)
    {
        $this->widgetProviderFilter->filter($queryBuilder, $widgetOptions);

        return $this->aclHelper->apply($queryBuilder);
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
