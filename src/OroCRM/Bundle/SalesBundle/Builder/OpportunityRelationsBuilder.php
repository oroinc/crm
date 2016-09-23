<?php

namespace Oro\Bundle\SalesBundle\Builder;

use Oro\Bundle\SalesBundle\Entity\Opportunity;

class OpportunityRelationsBuilder
{
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

    protected function buildCustomerContact(Opportunity $opportunity)
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
