<?php

namespace OroCRM\Bundle\SalesBundle\ImportExport\EventListener;

use Oro\Bundle\ImportExportBundle\Event\StrategyEvent;
use Oro\Bundle\SecurityBundle\SecurityFacade;

use OroCRM\Bundle\SalesBundle\Builder\OpportunityRelationsBuilder;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

class OpportunityListener
{
    /** @var SecurityFacade */
    protected $securityFacade;

    /**
     * @param SecurityFacade $securityFacade
     */
    public function __construct(SecurityFacade $securityFacade)
    {
        $this->securityFacade = $securityFacade;
    }

    /**
     * @param StrategyEvent $event
     */
    public function onProcessAfter(StrategyEvent $event)
    {
        $entity = $event->getEntity();
        if ($entity instanceof Opportunity) {
            $relationsBuilder = new OpportunityRelationsBuilder($this->securityFacade, $entity);
            $relationsBuilder->buildAll();
        }
    }
}
