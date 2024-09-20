<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Fixture;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CurrencyBundle\Entity\MultiCurrency;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOption;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

class LoadFullOpportunityFixtures extends AbstractOpportunityFixtures
{
    /**
     * {@inheritDoc}
     */
    protected function createOpportunity(ObjectManager $manager): void
    {
        $opportunity = new Opportunity();
        /**
         * Fix case when we test upgrade from 1.12
         * Fix this in CRM-8214
         */
        if ($manager->getClassMetadata(Opportunity::class)->hasAssociation('data_channel')) {
            $opportunity->setDataChannel($this->getReference('default_channel'));
        }
        $opportunity->setName('Full data opportunity');
        $opportunity->setCustomerAssociation($this->getReference('default_account_customer'));
        $opportunity->setOrganization($this->getOrganization());
        $opportunity->setOwner($this->getUser());
        $opportunity->setBudgetAmount(MultiCurrency::create(50, 'USD'));
        $opportunity->setCloseRevenue(MultiCurrency::create(0, 'USD'));
        $opportunity->setProbability(0.4);
        $opportunity->setStatus($manager->getReference(
            EnumOption::class,
            ExtendHelper::buildEnumOptionId(Opportunity::INTERNAL_STATUS_CODE, 'in_progress')
        ));

        $manager->persist($opportunity);
        $manager->flush();

        $this->setReference('full_opportunity', $opportunity);
    }
}
