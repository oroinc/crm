<?php

namespace OroCRM\Bundle\SalesBundle\EventListener;

use Oro\Bundle\SearchBundle\Event\BeforeSearchEvent;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\OrganizationBundle\Provider\BusinessUnitAclProvider;
use Oro\Bundle\SecurityBundle\Acl\Domain\OneShotIsGrantedObserver;

class ForecastWidgetBusinessUnitAutocompleteListener
{
    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var BusinessUnitAclProvider */
    protected $businessUnitAclProvider;

    /** @var string */
    protected $opportunityClassName;

    /**
     * @param SecurityFacade $securityFacade
     * @param BusinessUnitAclProvider $businessUnitAclProvider
     * @param string $opportunityClassName
     */
    public function __construct(
        SecurityFacade $securityFacade,
        BusinessUnitAclProvider $businessUnitAclProvider,
        $opportunityClassName
    ) {
        $this->securityFacade = $securityFacade;
        $this->businessUnitAclProvider = $businessUnitAclProvider;
        $this->opportunityClassName = $opportunityClassName;
    }

    /**
     * @param BeforeSearchEvent $event
     */
    public function onSearchBefore(BeforeSearchEvent $event)
    {
        $query = $event->getQuery();
        $from  = $query->getFrom();

        if (in_array('oro_business_unit', $from, true)) {
            $observer = new OneShotIsGrantedObserver();
            $criteria = $query->getCriteria();
            $expr = $criteria->expr();
            $businessUnitIds = $this
                ->businessUnitAclProvider
                ->addOneShotIsGrantedObserver($observer)
                ->getBusinessUnitIds($this->opportunityClassName, 'VIEW');
            $criteria->where($expr->eq('integer.organization', $this->securityFacade->getOrganizationId()));
            $criteria->andWhere($expr->in('integer.id', $businessUnitIds));
        }
    }
}
