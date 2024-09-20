<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Fixture;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CurrencyBundle\Entity\MultiCurrency;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOption;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

class LoadClosedOpportunityFixtures extends AbstractOpportunityFixtures
{
    /**
     * {@inheritDoc}
     */
    protected function createOpportunity(ObjectManager $manager): void
    {
        $opportunity = new Opportunity();
        $opportunity->setName('test_opportunity_closed');
        $opportunity->setCustomerAssociation($this->getReference('default_account_customer'));
        $opportunity->setOwner($this->getUser());
        $opportunity->setBudgetAmount(MultiCurrency::create(50, 'USD'));
        $opportunity->setCloseRevenue(MultiCurrency::create(100, 'USD'));
        $opportunity->setProbability(1);
        $opportunity->setOrganization($this->getOrganization());
        $opportunity->setCloseDate(new \DateTime());
        $opportunity->setStatus($manager->getReference(
            EnumOption::class,
            ExtendHelper::buildEnumOptionId(Opportunity::INTERNAL_STATUS_CODE, 'lost')
        ));

        $manager->persist($opportunity);
        $manager->flush();

        $this->setReference('lost_opportunity', $opportunity);
    }
}
