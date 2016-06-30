<?php

namespace OroCRM\Bundle\SalesBundle\Builder;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

class OpportunityRelationsBuilder
{
    /** @var Opportunity $opportunity */
    private $opportunity;

    /**
     * @param Opportunity $opportunity
     */
    public function __construct(Opportunity $opportunity)
    {
        $this->opportunity = $opportunity;
    }
    
    public function build()
    {
        $this->buildCustomer();
    }

    private function buildCustomer()
    {
        $customer = $this->opportunity->getCustomer();
        if (!$customer) {
            return;
        }

        if (!$customer->getDataChannel()) {
            // new customer needs a channel
            $customer->setDataChannel($this->opportunity->getDataChannel());
        }

        if (!$customer->getAccount()) {
            // new Account for new B2bCustomer
            $account = new Account();
            $account->setName($customer->getName());
            $customer->setAccount($account);
        }

        if (!$customer->getOrganization()) {
            $customer->setOrganization($this->opportunity->getOrganization());
        }
    }
}
