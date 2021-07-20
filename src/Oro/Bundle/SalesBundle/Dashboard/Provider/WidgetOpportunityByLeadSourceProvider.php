<?php

namespace Oro\Bundle\SalesBundle\Dashboard\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\DashboardBundle\Filter\DateFilterProcessor;
use Oro\Bundle\EntityExtendBundle\Twig\EnumExtension;
use Oro\Bundle\SalesBundle\Entity\Repository\OpportunityRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Provides chart data for 'Opportunity By Lead Source' dashboard widget
 */
class WidgetOpportunityByLeadSourceProvider
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var DateFilterProcessor */
    protected $dateFilterProcessor;

    /** @var TranslatorInterface */
    private $translator;

    /** @var EnumExtension */
    private $enumValueTranslator;

    /** @var CurrencyQueryBuilderTransformerInterface  */
    protected $qbTransformer;

    public function __construct(
        ManagerRegistry $doctrine,
        AclHelper $aclHelper,
        DateFilterProcessor $dateFilterProcessor,
        TranslatorInterface $translator,
        EnumExtension $enumValueTranslator,
        CurrencyQueryBuilderTransformerInterface $qbTransformer
    ) {
        $this->registry = $doctrine;
        $this->aclHelper = $aclHelper;
        $this->dateFilterProcessor = $dateFilterProcessor;
        $this->translator = $translator;
        $this->enumValueTranslator = $enumValueTranslator;
        $this->qbTransformer = $qbTransformer;
    }

    /**
     * Get chart data filtered by options
     *
     * @param array $dateRange ['start' => $dateString, 'end' => $dateString]
     * @param array $ownerIds
     * @param array $excluded
     * @param bool $byAmount
     *
     * @return array
     */
    public function getChartData(array $dateRange, array $ownerIds, array $excluded = [], $byAmount = false)
    {
        $rows = $this->getDataByType($dateRange, $ownerIds, $byAmount);

        if (empty($rows)) {
            return [];
        }

        $data = $this->processData($rows, $excluded);

        // translate sources
        foreach ($data as $key => $item) {
            $data[$key]['source'] = $this->translateSource($item['source']);
        }

        return $data;
    }

    /**
     * Get raw data from repository
     * @param array $dateRange
     * @param array $ownerIds
     * @param bool $byAmount Whether DataType is by Amount or by Count
     *
     * @return array
     */
    protected function getDataByType(array $dateRange, array $ownerIds, $byAmount = false)
    {
        $repo = $this->getOpportunityRepository();
        if ($byAmount) {
            return $this->getOpportunitiesAmountGroupByLeadSource(
                $dateRange,
                $ownerIds
            );
        }

        return $repo->getOpportunitiesCountGroupByLeadSource(
            $this->aclHelper,
            $this->dateFilterProcessor,
            $dateRange,
            $ownerIds
        );
    }

    /**
     * @param array $dateRange
     * @param array $ownerIds
     * @return array
     */
    protected function getOpportunitiesAmountGroupByLeadSource($dateRange, $ownerIds)
    {
        $repo = $this->getOpportunityRepository();
        $qb = $repo->getOpportunitiesGroupByLeadSourceQueryBuilder(
            $this->dateFilterProcessor,
            $dateRange,
            $ownerIds
        );

        $closeRevenueQuery = $this->qbTransformer->getTransformSelectQuery('closeRevenue', $qb);
        $budgetAmountQuery = $this->qbTransformer->getTransformSelectQuery('budgetAmount', $qb);
        $qb->addSelect(
            sprintf(
                "SUM(CASE WHEN o.status = 'won' THEN (%s) ELSE (%s) END) as value",
                $closeRevenueQuery,
                $budgetAmountQuery
            )
        );

        return $this->aclHelper->apply($qb)->getArrayResult();
    }

    /**
     * Process the data to create filtered list of sources that contains:
     * - Unclassified group that contains the data from unnamed sources
     * - $limit number of named groups sorted by value
     * - Others group that contains the data from the rest of the groups plus the excluded ones
     *
     * @param array $rows
     * @param array $excluded List of the sources to merge into Others
     * @param int $limit      Limit named groups, excess goes into Others
     *
     * @return array
     */
    protected function processData(array $rows, $excluded = [], $limit = 10)
    {
        // first sort by value to make sure biggest numbers are not merged to Others (when limit is applied)
        usort($rows, static fn ($a, $b) => $b['value'] <=> $a['value']);
        // get excluded sources (to be merged with Others)
        $others = array_filter(
            $rows,
            function ($row) use ($excluded) {
                return in_array($row['source'], $excluded);
            }
        );

        // remove the excluded sources
        $rows = array_diff_key($rows, $others);

        // get all named sources with non-zero values
        $named = array_filter(
            $rows,
            function ($row) {
                return $row['value'] > 0;
            }
        );

        // add a slice consisting of the first $limit classified sources
        $result = array_slice($named, 0, $limit);

        // merge the data from sources that left with the excluded sources
        $others = array_merge($others, array_slice($named, $limit));
        $othersCount = array_sum(array_column($others, 'value'));
        // add Others as last group
        if ($othersCount > 0) {
            $result[] = [
                'value' => $othersCount,
                'source' => '',
            ];
        }

        return $result;
    }

    /**
     * Translate lead_source enum string
     * null values are translated as 'unclassified', empty as 'others'
     *
     * @param string $source
     *
     * @return string
     */
    protected function translateSource($source)
    {
        if (null === $source) {
            return $this->translator->trans('oro.sales.lead.source.unclassified');
        }

        if ('' === $source) {
            return $this->translator->trans('oro.sales.lead.source.others');
        }

        return $this->enumValueTranslator->transEnum($source, 'lead_source');
    }

    /**
     * @return OpportunityRepository
     */
    protected function getOpportunityRepository()
    {
        return $this->registry->getRepository('OroSalesBundle:Opportunity');
    }
}
