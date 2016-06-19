<?php

namespace OroCRM\Bundle\SalesBundle\Dashboard\Provider;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Component\DoctrineUtils\ORM\QueryUtils;
use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\DashboardBundle\Filter\DateFilterProcessor;
use Oro\Bundle\UserBundle\Dashboard\OwnerHelper;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\EntityExtendBundle\Provider\EnumValueProvider;

use OroCRM\Bundle\SalesBundle\Entity\Repository\OpportunityRepository;

class OpportunityByStatusProvider
{
    /** @var RegistryInterface */
    protected $registry;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var DateFilterProcessor */
    protected $dateFilterProcessor;

    /** @var EnumValueProvider */
    protected $enumValueProvider;

    /** @var OwnerHelper */
    protected $ownerHelper;

    /**
     * @param RegistryInterface   $doctrine
     * @param AclHelper           $aclHelper
     * @param DateFilterProcessor $processor
     * @param EnumValueProvider   $enumValueProvider
     * @param OwnerHelper         $ownerHelper
     */
    public function __construct(
        RegistryInterface $doctrine,
        AclHelper $aclHelper,
        DateFilterProcessor $processor,
        EnumValueProvider $enumValueProvider,
        OwnerHelper $ownerHelper
    ) {
        $this->registry            = $doctrine;
        $this->aclHelper           = $aclHelper;
        $this->dateFilterProcessor = $processor;
        $this->enumValueProvider   = $enumValueProvider;
        $this->ownerHelper         = $ownerHelper;
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     *
     * @return array
     */
    public function getOpportunitiesGroupedByStatus(WidgetOptionBag $widgetOptions)
    {
        $dateRange        = $widgetOptions->get('dateRange');
        $owners           = $this->ownerHelper->getOwnerIds($widgetOptions);
        $excludedStatuses = $widgetOptions->get('excluded_statuses', []);
        $orderBy          = $widgetOptions->get('useQuantityAsData') ? 'quantity' : 'budget';
        $qb               = $this->getOpportunityRepository()
            ->getGroupedOpportunitiesByStatusQB('o', $excludedStatuses, $orderBy);
        $this->dateFilterProcessor->process($qb, $dateRange, 'o.createdAt');
        if ($owners) {
            QueryUtils::applyOptimizedIn($qb, 'o.owner', $owners);
        }
        $groupedData = $this->aclHelper->apply($qb)->getArrayResult();

        return $this->getStatusesResultData($groupedData, $excludedStatuses);
    }

    /**
     * @return OpportunityRepository
     */
    protected function getOpportunityRepository()
    {
        return $this->registry->getRepository('OroCRMSalesBundle:Opportunity');
    }

    /**
     * @param $groupedData
     * @param $excludedStatuses
     *
     * @return array
     */
    protected function getStatusesResultData(array $groupedData, array $excludedStatuses)
    {
        $resultData   = [];
        $statusesData = [];
        $statuses     = $this->enumValueProvider->getEnumChoicesByCode('opportunity_status');

        foreach ($statuses as $key => $name) {
            if (!in_array($key, $excludedStatuses)) {
                $statusesData[$key] = [
                    'name'     => $key,
                    'label'    => $name,
                    'budget'   => 0,
                    'quantity' => 0
                ];
            }
        }
        foreach ($groupedData as $statusData) {
            $budget = $statusData['name'] === 'won'
                ? (float)$statusData['revenue']
                : (float)$statusData['budget'];
            if ($budget) {
                $statusData['label'] = $statusesData[$statusData['name']]['label'];
                $resultData[]        = $statusData;
                unset($statusesData[$statusData['name']]);
            }
        }

        return $resultData + $statusesData;
    }
}
