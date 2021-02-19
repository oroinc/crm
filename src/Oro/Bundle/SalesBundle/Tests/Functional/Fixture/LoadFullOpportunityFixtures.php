<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Fixture;

use Oro\Bundle\CurrencyBundle\Entity\MultiCurrency;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

class LoadFullOpportunityFixtures extends AbstractOpportunityFixtures
{
    /**
     * @return void
     */
    protected function createOpportunity()
    {
        $opportunity = new Opportunity();

        $classMetaData = $this->em->getClassMetadata(Opportunity::class);
        /**
         * Fix case when we test upgrade from 1.12
         */
        if ($classMetaData->hasAssociation('data_channel')) {
            $opportunity->setDataChannel($this->getReference('default_channel'));
        }

        $opportunity->setName('Full data opportunity');

        $opportunity->setCustomerAssociation($this->getReference('default_account_customer'));
        $opportunity->setOrganization($this->getOrganization());
        $opportunity->setOwner($this->getUser());

        $budgetAmount = MultiCurrency::create(50, 'USD');
        $opportunity->setBudgetAmount($budgetAmount);

        $closeRevenue = MultiCurrency::create(0, 'USD');
        $opportunity->setCloseRevenue($closeRevenue);

        $opportunity->setProbability(0.4);

        $enumClass = ExtendHelper::buildEnumValueClassName(Opportunity::INTERNAL_STATUS_CODE);
        $opportunity->setStatus($this->em->getReference($enumClass, 'in_progress'));

        $this->em->persist($opportunity);
        $this->em->flush();

        $this->setReference('full_opportunity', $opportunity);
    }
}
