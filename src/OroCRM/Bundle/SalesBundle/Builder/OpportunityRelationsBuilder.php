<?php

namespace OroCRM\Bundle\SalesBundle\Builder;

use Oro\Bundle\SecurityBundle\SecurityFacade;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

class OpportunityRelationsBuilder
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

    public function buildAll(Opportunity $opportunity)
    {
        $this->buildCustomer($opportunity);
        $this->buildAccount($opportunity);
    }

    public function buildCustomer(Opportunity $opportunity)
    {
        $customer = $opportunity->getCustomer();
        if (!$customer) {
            return;
        }

        if (!$customer->getDataChannel()) {
            // new customer needs a channel
            $customer->setDataChannel($opportunity->getDataChannel());
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
            $customer->setOrganization($opportunity->getOrganization());
        }

        $this->buildCustomerContact($opportunity);
    }

    public function buildAccount(Opportunity $opportunity)
    {
        $customer = $opportunity->getCustomer();
        if (!$customer) {
            return;
        }

        $contact = $opportunity->getContact();
        $account = $customer->getAccount();

        if (!$contact || !$account) {
            return;
        }

        if (!$contact->getId() || !$account->getId()) {
            $account->addContact($contact);
        }
    }

    private function buildCustomerContact(Opportunity $opportunity)
    {
        $customer           = $opportunity->getCustomer();
        $opportunityContact = $opportunity->getContact();

        if (!$customer || !$opportunityContact || $customer->getContact()) {
            return;
        }

        // if either object is new, auto set customer contact
        if (!$customer->getId() || !$opportunityContact->getId()) {
            $customer->setContact($opportunityContact);
        }
    }
}
