<?php

namespace Oro\Bundle\SalesBundle\Provider\Opportunity;

use Doctrine\ORM\QueryBuilder;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\DashboardBundle\Filter\WidgetProviderFilter;
use Oro\Bundle\LocaleBundle\Formatter\NumberFormatter;
use Oro\Bundle\QueryDesignerBundle\QueryDesigner\FilterProcessor;
use Oro\Bundle\SalesBundle\Entity\Repository\OpportunityRepository;

class IndeterminateForecastProvider
{
    /** @var RegistryInterface */
    protected $doctrine;

    /** @var WidgetProviderFilter */
    protected $widgetProviderFilter;

    /** @var FilterProcessor */
    protected $filterProcessor;

    /** @var NumberFormatter */
    protected $numberFormatter;

    /** @var CurrencyQueryBuilderTransformerInterface  */
    protected $qbTransformer;

    /** @var array */
    protected $data = [];

    /**
     * @param RegistryInterface $doctrine
     * @param WidgetProviderFilter $widgetProviderFilter
     * @param FilterProcessor $filterProcessor
     * @param NumberFormatter $numberFormatter
     * @param CurrencyQueryBuilderTransformerInterface $qbTransformer
     */
    public function __construct(
        RegistryInterface $doctrine,
        WidgetProviderFilter $widgetProviderFilter,
        FilterProcessor $filterProcessor,
        NumberFormatter $numberFormatter,
        CurrencyQueryBuilderTransformerInterface $qbTransformer
    ) {
        $this->doctrine = $doctrine;
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
            $qb = $this->getForcastQueryBuilder($widgetOptions->get('queryFilter', []));

            $result = $this->widgetProviderFilter->filter($qb, $widgetOptions)->getOneOrNullResult()
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
    protected function getForcastQueryBuilder($queryFilter = null)
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
        $ownerIds = $this->widgetProviderFilter->getOwnerIds($widgetOptions);

        return md5(serialize([$ownerIds, $widgetOptions->get('queryFilter', [])]));
    }
}
