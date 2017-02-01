<?php

namespace OroCRM\Bundle\SalesBundle\Dashboard\Provider;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Component\DoctrineUtils\ORM\QueryUtils;

use Oro\Bundle\DashboardBundle\Filter\DateFilterProcessor;
use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\UserBundle\Dashboard\OwnerHelper;

use OroCRM\Bundle\SalesBundle\Entity\Repository\OpportunityRepository;

class OpportunityByStatusProvider
{
    /** @var RegistryInterface */
    protected $registry;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var DateFilterProcessor */
    protected $dateFilterProcessor;

    /** @var OwnerHelper */
    protected $ownerHelper;

    /**
     * @param RegistryInterface   $doctrine
     * @param AclHelper           $aclHelper
     * @param DateFilterProcessor $processor
     * @param OwnerHelper         $ownerHelper
     */
    public function __construct(
        RegistryInterface $doctrine,
        AclHelper $aclHelper,
        DateFilterProcessor $processor,
        OwnerHelper $ownerHelper
    ) {
        $this->registry            = $doctrine;
        $this->aclHelper           = $aclHelper;
        $this->dateFilterProcessor = $processor;
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
        /**
         * Excluded statuses will be filtered from result in method `formatResult` below.
         * Due to performance issues with `NOT IN` clause in database.
         */
        $excludedStatuses = $widgetOptions->get('excluded_statuses', []);
        $orderBy          = $widgetOptions->get('useQuantityAsData') ? 'quantity' : 'budget';

        /** @var OpportunityRepository $opportunityRepository */
        $opportunityRepository = $this->registry->getRepository('OroCRMSalesBundle:Opportunity');
        $qb = $opportunityRepository->createQueryBuilder('o')
            ->select('IDENTITY (o.status) status')
            ->groupBy('status')
            ->orderBy($orderBy, 'DESC');

        switch ($orderBy) {
            case 'quantity':
                $qb->addSelect('COUNT(o.id) as quantity');
                break;
            case 'budget':
                $qb->addSelect(
                    'SUM(
                        CASE WHEN o.status = \'won\'
                            THEN (CASE WHEN o.closeRevenue IS NOT NULL THEN o.closeRevenue ELSE 0 END)
                            ELSE (CASE WHEN o.budgetAmount IS NOT NULL THEN o.budgetAmount ELSE 0 END)
                        END
                    ) as budget'
                );
        }

        $this->dateFilterProcessor->applyDateRangeFilterToQuery($qb, $dateRange, 'o.createdAt');

        if ($owners) {
            QueryUtils::applyOptimizedIn($qb, 'o.owner', $owners);
        }

        $result = $this->aclHelper->apply($qb)->getArrayResult();

        return $this->formatResult($result, $excludedStatuses, $orderBy);
    }

    /**
     * @param array    $result
     * @param string[] $excludedStatuses
     * @param string   $orderBy
     *
     * @return array
     */
    protected function formatResult($result, $excludedStatuses, $orderBy)
    {
        $resultStatuses = array_flip(array_column($result, 'status', null));

        foreach ($this->getAvailableOpportunityStatuses() as $statusKey => $statusLabel) {
            $resultIndex = isset($resultStatuses[$statusKey]) ? $resultStatuses[$statusKey] : null;
            if (in_array($statusKey, $excludedStatuses)) {
                if (null !== $resultIndex) {
                    unset($result[$resultIndex]);
                }
                continue;
            }

            if (null !== $resultIndex) {
                $result[$resultIndex]['label'] = $statusLabel;
            } else {
                $result[] = [
                    'status' => $statusKey,
                    'label'  => $statusLabel,
                    $orderBy => 0
                ];
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function getAvailableOpportunityStatuses()
    {
        /** @var EnumValueRepository $statusesRepository */
        $statusesRepository = $this->registry->getRepository(
            ExtendHelper::buildEnumValueClassName('opportunity_status')
        );
        $statuses = $statusesRepository->createQueryBuilder('s')
            ->select('s.id, s.name')
            ->getQuery()
            ->getArrayResult();

        return array_column($statuses, 'name', 'id');
    }
}
