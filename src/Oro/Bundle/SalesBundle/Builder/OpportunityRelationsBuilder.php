<?php

namespace Oro\Bundle\SalesBundle\Builder;

use Oro\Bundle\ChannelBundle\Model\ChannelAwareInterface;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
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
        if (!$opportunity->getCustomerAssociation()) {
            return;
        }

        $customer = $opportunity->getCustomerAssociation()->getTarget();

        if ($customer instanceof ChannelAwareInterface && !$customer->getDataChannel()) {
            // new customer needs a channel
            $customer->setDataChannel($opportunity->getDataChannel());
        }

        if (!$customer->getOrganization()) {
            $customer->setOrganization($opportunity->getOrganization());
        }

        if ($customer instanceof B2bCustomer) {
            $this->buildCustomerContact($opportunity);
        }
    }

    public function buildAccount(Opportunity $opportunity)
    {
        if (!$opportunity->getCustomerAssociation()) {
            return;
        }
        $customer = $opportunity->getCustomerAssociation();
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
        $customer           = $opportunity->getCustomerAssociation()->getTarget();
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
