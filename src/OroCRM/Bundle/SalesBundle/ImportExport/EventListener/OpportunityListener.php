<?php

namespace OroCRM\Bundle\SalesBundle\ImportExport\EventListener;

use Oro\Bundle\ImportExportBundle\Event\StrategyEvent;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use OroCRM\Bundle\SalesBundle\Builder\OpportunityRelationsBuilder;

class OpportunityListener
{
    /** @var OpportunityRelationsBuilder */
    protected $relationsBuilder;

    /**
     * @param OpportunityRelationsBuilder $relationsBuilder
     */
    public function __construct(OpportunityRelationsBuilder $relationsBuilder)
    {
        $this->relationsBuilder = $relationsBuilder;
    }

    /**
     * @param StrategyEvent $event
     */
    public function onProcessAfter(StrategyEvent $event)
    {
        $entity = $event->getEntity();
        if ($entity instanceof Opportunity) {
            $this->relationsBuilder->buildAll($entity);
        }
    }
}
