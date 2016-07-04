<?php

namespace OroCRM\Bundle\SalesBundle\Dashboard\Provider;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\DashboardBundle\Filter\DateFilterProcessor;
use Oro\Bundle\EntityExtendBundle\Twig\EnumExtension;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

use OroCRM\Bundle\SalesBundle\Entity\Repository\LeadRepository;

/**
 * Provides chart data for 'Opportunity By Lead Source' dashboard widget
 */
class WidgetOpportunityByLeadSourceProvider
{
    /** @var RegistryInterface */
    protected $registry;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var DateFilterProcessor */
    protected $dateFilterProcessor;

    /** @var TranslatorInterface */
    private $translator;

    /** @var EnumExtension */
    private $enumValueTranslator;

    /**
     * @param RegistryInterface $doctrine
     * @param AclHelper $aclHelper
     * @param DateFilterProcessor $processor
     * @param TranslatorInterface $translator
     * @param EnumExtension $enumValueTranslator
     */
    public function __construct(
        RegistryInterface $doctrine,
        AclHelper $aclHelper,
        DateFilterProcessor $processor,
        TranslatorInterface $translator,
        EnumExtension $enumValueTranslator
    ) {
        $this->registry = $doctrine;
        $this->aclHelper = $aclHelper;
        $this->dateFilterProcessor = $processor;
        $this->translator = $translator;
        $this->enumValueTranslator = $enumValueTranslator;
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
        $repo = $this->getLeadRepository();
        if ($byAmount) {
            return $repo->getOpportunitiesAmountGroupByLeadSource($this->aclHelper, $dateRange, $ownerIds);
        }

        return $repo->getOpportunitiesCountGroupByLeadSource($this->aclHelper, $dateRange, $ownerIds);
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
        $result = [];

        // first sort by value to make sure biggest numbers are not merged to Others (when limit is applied)
        usort(
            $rows,
            function ($a, $b) {
                if ($a['value'] === $b['value']) {
                    return 0;
                }

                return $a['value'] < $b['value'] ? 1 : -1;
            }
        );
        // get excluded sources (to be merged with Others)
        $others = array_filter(
            $rows,
            function ($row) use ($excluded) {
                return in_array($row['source'], $excluded);
            }
        );

        // remove the excluded sources
        $rows = array_diff_key($rows, $others);

        // get the sum of value column for unclassified sources (i.e. source is empty string)
        $unclassifiedCount = array_reduce(
            $rows,
            function ($count, $row) {
                return '' === $row['source'] ? $count + $row['value'] : $count;
            }
        );

        // add the Unclassified group on top
        if ($unclassifiedCount > 0) {
            $result[] = [
                'value' => $unclassifiedCount,
                'source' => null,
            ];
        }

        // get all named sources with non-zero values
        $named = array_filter(
            $rows,
            function ($row) {
                return !empty($row['source']) && $row['value'] > 0;
            }
        );

        // add a slice consisting of the first $limit classified sources
        $result = array_merge($result, array_slice($named, 0, $limit));

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
        if (!empty($source)) {
            return $this->enumValueTranslator->transEnum($source, 'lead_source');
        }

        if (null === $source) {
            return $this->translator->trans('orocrm.sales.lead.source.unclassified');
        }

        return $this->translator->trans('orocrm.sales.lead.source.others');
    }

    /**
     * @return LeadRepository
     */
    protected function getLeadRepository()
    {
        return $this->registry->getRepository('OroCRMSalesBundle:Lead');
    }
}
