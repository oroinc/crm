<?php

namespace OroCRM\Bundle\SalesBundle\ImportExport\EventListener;

use Oro\Bundle\ImportExportBundle\Event\StrategyEvent;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

class OpportunityListener
{
    /**
     * @param StrategyEvent $event
     */
    public function onProcessAfter(StrategyEvent $event)
    {
        $entity = $event->getEntity();

        if (!$entity instanceof Opportunity) {
            return;
        }
        $b2bCustomer = $entity->getCustomer();
        if ($b2bCustomer) {
            if (!$b2bCustomer->getDataChannel()) {
                // new customer needs a channel
                $b2bCustomer->setDataChannel($entity->getDataChannel());
            }
            if (!$b2bCustomer->getAccount()) {
                // new Account for new B2bCustomer
                $account = new Account();
                $account->setName($b2bCustomer->getName());
                $b2bCustomer->setAccount($account);
            }
            if (!$b2bCustomer->getOrganization()) {
                $b2bCustomer->setOrganization($entity->getOrganization());
            }
        }
    }
}
