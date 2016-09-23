<?php

namespace Oro\Bundle\SalesBundle\Dashboard\Provider;

use Doctrine\ORM\Query\Expr as Expr;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Component\DoctrineUtils\ORM\QueryUtils;
use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\DashboardBundle\Filter\DateFilterProcessor;
use Oro\Bundle\UserBundle\Dashboard\OwnerHelper;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\SalesBundle\Entity\Repository\OpportunityRepository;

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

        if ($owners) {
            QueryUtils::applyOptimizedIn($qb, 'o.owner', $owners);
        }

        // move previously applied conditions into join
        // since we don't want to exclude any statuses from result
        $joinConditions = $qb->getDQLPart('where');
        if ($joinConditions) {
            $whereParts = (string) $joinConditions;
            $qb->resetDQLPart('where');

            $join = $qb->getDQLPart('join')['s'][0];
            $qb->resetDQLPart('join');

            $qb->add(
                'join',
                [
                    's' => new Expr\Join(
                        $join->getJoinType(),
                        $join->getJoin(),
                        $join->getAlias(),
                        $join->getConditionType(),
                        sprintf('%s AND (%s)', $join->getCondition(), $whereParts),
                        $join->getIndexBy()
                    )
                ],
                true
            );
        }

        if ($excludedStatuses) {
            $qb->andWhere(
                $qb->expr()->notIn('s.id', $excludedStatuses)
            );
        }

        return $this->aclHelper->apply($qb)->getArrayResult();
    }

    /**
     * @return OpportunityRepository
     */
    protected function getOpportunityRepository()
    {
        return $this->registry->getRepository('OroSalesBundle:Opportunity');
    }
}
