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
    
    public function buildAll()
    {
        $this->buildCustomer();
        $this->buildAccount();
    }

    public function buildCustomer()
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

    public function buildAccount()
    {
        $customer = $this->opportunity->getCustomer();
        if (!$customer) {
            return;
        }

        $contact = $this->opportunity->getContact();
        $account = $customer->getAccount();

        if (!$contact || !$account) {
            return;
        }

        if (!$contact->getId() || !$account->getId()) {
            $account->addContact($contact);
        }
    }
}
