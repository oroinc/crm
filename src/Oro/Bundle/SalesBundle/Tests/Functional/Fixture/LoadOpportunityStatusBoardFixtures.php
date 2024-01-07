<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CurrencyBundle\Entity\MultiCurrency;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;

/**
 * Fixtures for opportunity status board test
 */
class LoadOpportunityStatusBoardFixtures extends AbstractFixture implements DependentFixtureInterface
{
    private const OPPORTUNITY_COUNT = 25;
    private const STATUSES_COUNT = 4;

    /**
     * {@inheritDoc}
     */
    public function getDependencies(): array
    {
        return [LoadOrganization::class];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        $opportunityStatuses = ['in_progress', 'lost', 'needs_analysis', 'won'];
        for ($i = 0; $i < self::OPPORTUNITY_COUNT; $i++) {
            $opportunity = new Opportunity();
            $opportunity->setName('opname_' . $i);
            $opportunity->setBudgetAmount(MultiCurrency::create(50.00, 'USD'));
            $opportunity->setProbability(0.1);
            $opportunity->setOrganization($this->getReference(LoadOrganization::ORGANIZATION));
            $statusName = $opportunityStatuses[$i % self::STATUSES_COUNT];
            $opportunity->setStatus($manager->getReference(
                ExtendHelper::buildEnumValueClassName(Opportunity::INTERNAL_STATUS_CODE),
                $statusName
            ));
            $manager->persist($opportunity);
            $manager->flush();
        }
    }
}
