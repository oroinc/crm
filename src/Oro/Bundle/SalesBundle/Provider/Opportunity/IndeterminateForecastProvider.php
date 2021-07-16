<?php

namespace Oro\Bundle\SalesBundle\Provider\Opportunity;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\DashboardBundle\Filter\WidgetProviderFilterManager;
use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\LocaleBundle\Formatter\NumberFormatter;
use Oro\Bundle\SalesBundle\Entity\Repository\OpportunityRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\SegmentBundle\Query\FilterProcessor;

/**
 * Provides data for Forecast widget.
 */
class IndeterminateForecastProvider
{
    /** @var ManagerRegistry */
    protected $doctrine;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var WidgetProviderFilterManager */
    protected $widgetProviderFilter;

    /** @var FilterProcessor */
    protected $filterProcessor;

    /** @var NumberFormatter */
    protected $numberFormatter;

    /** @var CurrencyQueryBuilderTransformerInterface  */
    protected $qbTransformer;

    /** @var array */
    protected $data = [];

    public function __construct(
        ManagerRegistry $doctrine,
        AclHelper $aclHelper,
        WidgetProviderFilterManager $widgetProviderFilter,
        FilterProcessor $filterProcessor,
        NumberFormatter $numberFormatter,
        CurrencyQueryBuilderTransformerInterface $qbTransformer
    ) {
        $this->doctrine = $doctrine;
        $this->aclHelper = $aclHelper;
        $this->widgetProviderFilter = $widgetProviderFilter;
        $this->filterProcessor = $filterProcessor;
        $this->numberFormatter = $numberFormatter;
        $this->qbTransformer = $qbTransformer;
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     * @param string          $dataKey
     *
     * @return string
     */
    public function getForecastOfOpportunitiesValues(WidgetOptionBag $widgetOptions, $dataKey)
    {
        $data = $this->getIndeterminateData($widgetOptions);

        return [
            'value' => $this->numberFormatter->formatCurrency($data[$dataKey]),
        ];
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     *
     * @return mixed ['totalIndeterminate' => <double>, 'weightedIndeterminate' => <double>]
     */
    public function getIndeterminateData(WidgetOptionBag $widgetOptions)
    {
        $cacheKey = $this->getCacheKey($widgetOptions);

        if (!isset($this->data[$cacheKey])) {
            $qb = $this->getForecastQueryBuilder($widgetOptions->get('queryFilter', []));

            $this->widgetProviderFilter->filter($qb, $widgetOptions);

            $result = $this->aclHelper->apply($qb)->getOneOrNullResult()
                ?: ['budgetAmount' => 0, 'weightedForecast' => 0];
            $this->data[$cacheKey] = [
                'totalIndeterminate'    => $result['budgetAmount'],
                'weightedIndeterminate' => $result['weightedForecast'],
            ];
        }

        return $this->data[$cacheKey];
    }

    /**
     * @return OpportunityRepository
     */
    protected function getOpportunityRepository()
    {
        return $this->doctrine->getRepository('OroSalesBundle:Opportunity');
    }

    /**
     * @param array|null $queryFilter
     *
     * @return QueryBuilder
     */
    protected function getForecastQueryBuilder($queryFilter = null)
    {
        $filters = isset($queryFilter['definition']['filters'])
            ? $queryFilter['definition']['filters']
            : [];

        $alias = 'o';
        return $this->filterProcessor
            ->process(
                $this->getOpportunityRepository()
                    ->getForecastQB($this->qbTransformer, $alias)
                    ->andWhere(sprintf('%s.closeDate IS NULL', $alias)),
                'Oro\Bundle\SalesBundle\Entity\Opportunity',
                $filters,
                $alias
            );
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     *
     * @return mixed
     */
    protected function getCacheKey(WidgetOptionBag $widgetOptions)
    {
        return md5(serialize($widgetOptions));
    }
}
