<?php

namespace Oro\Bundle\SalesBundle\ImportExport\EventListener;

use Oro\Bundle\ImportExportBundle\Event\StrategyEvent;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Builder\OpportunityRelationsBuilder;

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
        if (!$entity instanceof Opportunity) {
            return;
        }

        $customer = $entity->getCustomer();
        if ($customer) {
            if (!$customer->getAccount()) {
                // new Account for new B2bCustomer
                $account = new Account();
                $account->setName($customer->getName());
                $customer->setAccount($account);
            }
        }
        $this->relationsBuilder->buildAll($entity);
    }
}
