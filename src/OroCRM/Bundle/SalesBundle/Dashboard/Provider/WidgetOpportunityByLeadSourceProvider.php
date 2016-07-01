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
 *
 * @package OroCRM\Bundle\SalesBundle\Dashboard\Provider
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
    public function getOpportunityByLeadSourceData(array $dateRange, array $ownerIds, array $excluded = [], $byAmount = false)
    {
        $repo = $this->getLeadRepository();
        if ($byAmount) {
            $data = $repo->getOpportunitiesAmountByLeadSource($this->aclHelper, 10, $dateRange, $ownerIds);
        } else {
            $data = $repo->getOpportunitiesCountByLeadSource($this->aclHelper, 10, $dateRange, $ownerIds);
        }

        if (empty($data)) {
            return [];
        }

        $data = $this->processOpportunitiesByLeadSource($data, $excluded);

        // translate sources
        foreach ($data as $key => $item) {
            $data[$key]['source'] = $this->translateSource($item['source']);
        }

        return $data;
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
    protected function processOpportunitiesByLeadSource(array $rows, $excluded = [], $limit = 10)
    {
        $result = [];

        // sort by count to make sure biggest numbers are not merged to others
        usort(
            $rows,
            function ($a, $b) {
                if ($a['itemCount'] === $b['itemCount']) {
                    return 0;
                }

                return $a['itemCount'] < $b['itemCount'] ? 1 : -1;
            }
        );
        // get excluded sources (to be merged with others)
        $others = array_filter(
            $rows,
            function ($row) use ($excluded) {
                return in_array($row['source'], $excluded);
            }
        );

        // remove the excluded sources
        $rows = array_diff_key($rows, $others);

        // get a sum of itemCount for unclassified sources
        $unclassifiedCount = array_reduce(
            $rows,
            function ($count, $row) {
                return '' === $row['source'] ? $count + $row['itemCount'] : $count;
            }
        );

        // add Unclassified on top
        if ($unclassifiedCount > 0) {
            $result[] = [
                'itemCount' => $unclassifiedCount,
                'source' => null,
            ];
        }

        // get all named sources with non-zero values
        $named = array_filter(
            $rows,
            function ($row) {
                return !empty($row['source']) && $row['itemCount'] > 0;
            }
        );

        // add a slice consisting of the first $limit classified sources
        $result = array_merge($result, array_slice($named, 0, $limit));

        // merge the data from sources that left with the excluded sources
        $others = array_merge($others, array_slice($named, $limit));
        $othersCount = array_sum(array_column($others, 'itemCount'));
        // add Others as last group
        if ($othersCount > 0) {
            $result[] = [
                'itemCount' => $othersCount,
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
    private function translateSource($source)
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
    private function getLeadRepository()
    {
        return $this->registry->getRepository('OroCRMSalesBundle:Lead');
    }
}
