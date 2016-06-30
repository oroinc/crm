<?php

namespace OroCRM\Bundle\SalesBundle\Dashboard\Provider;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Component\DoctrineUtils\ORM\QueryUtils;
use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\DashboardBundle\Filter\DateFilterProcessor;
use Oro\Bundle\UserBundle\Dashboard\OwnerHelper;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

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
        $excludedStatuses = $widgetOptions->get('excluded_statuses', []);
        $orderBy          = $widgetOptions->get('useQuantityAsData') ? 'quantity' : 'budget';
        $qb               = $this->getOpportunityRepository()
            ->getGroupedOpportunitiesByStatusQB('o', $orderBy);
        $this->dateFilterProcessor->process($qb, $dateRange, 'o.createdAt');

        // Ignore filters by opportunities, if filters by date exists.
        $where = $qb->getDQLPart('where');
        if ($where) {
            $qb->where(
                $qb->expr()->orX(
                    $where,
                    $qb->expr()->isNull('o.id')
                )
            );
        }

        if ($excludedStatuses) {
            $qb->andWhere(
                $qb->expr()->notIn('s.id', $excludedStatuses)
            );
        }

        if ($owners) {
            QueryUtils::applyOptimizedIn($qb, 'o.owner', $owners);
        }
        return $this->aclHelper->apply($qb)->getArrayResult();
    }

    /**
     * @return OpportunityRepository
     */
    protected function getOpportunityRepository()
    {
        return $this->registry->getRepository('OroCRMSalesBundle:Opportunity');
    }
}
