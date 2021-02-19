<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Fixture;

use Oro\Bundle\CurrencyBundle\Entity\MultiCurrency;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

class LoadClosedOpportunityFixtures extends AbstractOpportunityFixtures
{
    /**
     * @return void
     */
    protected function createOpportunity()
    {
        $opportunity = new Opportunity();

        $opportunity->setName('test_opportunity_closed');

        $opportunity->setCustomerAssociation($this->getReference('default_account_customer'));
        $opportunity->setOwner($this->getUser());

        $budgetAmount = MultiCurrency::create(50, 'USD');
        $opportunity->setBudgetAmount($budgetAmount);

        $closeRevenue = MultiCurrency::create(100, 'USD');
        $opportunity->setCloseRevenue($closeRevenue);

        $opportunity->setProbability(1);
        $opportunity->setOrganization($this->getOrganization());
        $opportunity->setCloseDate(new \DateTime());

        $enumClass = ExtendHelper::buildEnumValueClassName(Opportunity::INTERNAL_STATUS_CODE);
        $opportunity->setStatus($this->em->getReference($enumClass, 'lost'));

        $this->em->persist($opportunity);
        $this->em->flush();

        $this->setReference('lost_opportunity', $opportunity);
    }
}
