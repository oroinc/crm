<?php

namespace OroCRM\Bundle\SalesBundle\ImportExport\EventListener;

use Oro\Bundle\ImportExportBundle\Event\StrategyEvent;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\SalesBundle\Builder\OpportunityRelationsBuilder;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

class OpportunityListener
{
    /**
     * @param StrategyEvent $event
     */
    public function onProcessAfter(StrategyEvent $event)
    {
        $entity = $event->getEntity();
        if ($entity instanceof Opportunity) {
            $relationsBuilder = new OpportunityRelationsBuilder($entity);
            $relationsBuilder->buildAll();
        }
    }
}
