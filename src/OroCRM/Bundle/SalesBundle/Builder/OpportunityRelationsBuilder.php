<?php

namespace OroCRM\Bundle\SalesBundle\Builder;

use Oro\Bundle\SecurityBundle\SecurityFacade;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

class OpportunityRelationsBuilder
{
    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var Opportunity $opportunity */
    protected $opportunity;

    /**
     * @param SecurityFacade $securityFacade
     * @param Opportunity    $opportunity
     */
    public function __construct(SecurityFacade $securityFacade, Opportunity $opportunity)
    {
        $this->securityFacade = $securityFacade;
        $this->opportunity    = $opportunity;
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
            if ($this->securityFacade->isGranted('CREATE', sprintf('Entity:%s', Account::class))) {
                // new Account for new B2bCustomer
                $account = new Account();
                $account->setName($customer->getName());
                $customer->setAccount($account);
            }
        }

        if (!$customer->getOrganization()) {
            $customer->setOrganization($this->opportunity->getOrganization());
        }

        $this->buildCustomerContact();
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

    private function buildCustomerContact()
    {
        $customer = $this->opportunity->getCustomer();
        $opportunityContact = $this->opportunity->getContact();

        if (!$customer || !$opportunityContact || $customer->getContact()) {
            return;
        }

        // if either object is new, auto set customer contact
        if (!$customer->getId() || !$opportunityContact->getId()) {
            $customer->setContact($opportunityContact);
        }
    }
}
