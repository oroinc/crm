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
     * @param bool $byAmount
     *
     * @return array
     */
    public function getOpportunityByLeadSourceData(array $dateRange, array $ownerIds, $byAmount = false)
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

        // translate sources
        foreach ($data as $key => $item) {
            $data[$key]['source'] = $this->translateSource($item['source']);
        }

        // sort alphabetically by label
        usort(
            $data,
            function ($a, $b) {
                return strcasecmp($a['source'], $b['source']);
            }
        );

        return $data;
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
