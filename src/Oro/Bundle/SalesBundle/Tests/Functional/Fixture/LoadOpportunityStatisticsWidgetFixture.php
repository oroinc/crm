<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CurrencyBundle\Entity\MultiCurrency;
use Oro\Bundle\DashboardBundle\Entity\Dashboard;
use Oro\Bundle\DashboardBundle\Entity\Widget;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOption;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Bundle\UserBundle\Entity\User;

class LoadOpportunityStatisticsWidgetFixture extends AbstractFixture implements DependentFixtureInterface
{
    #[\Override]
    public function getDependencies(): array
    {
        return [LoadOrganization::class];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $this->createOpportunities($manager);

        $dashboard = new Dashboard();
        $dashboard->setName('dashboard');
        $opportunityStaticsWidget = new Widget();
        $opportunityStaticsWidget->setDashboard($dashboard);
        $opportunityStaticsWidget->setName('opportunity_statistics');
        $opportunityStaticsWidget->setLayoutPosition([1, 1]);
        $dashboard->addWidget($opportunityStaticsWidget);
        if (!$this->hasReference('widget_opportunity_statistics')) {
            $this->setReference('widget_opportunity_statistics', $opportunityStaticsWidget);
        }
        $manager->persist($dashboard);
        $manager->flush();
    }

    private function createOpportunities(ObjectManager $manager): void
    {
        $owner = new User();
        $owner->setId(18);
        $owner->setUsername('owner');
        $owner->setEmail('owner@example.com');
        $owner->setPassword('secrecy');
        $manager->persist($owner);

        $firstOppo  = $this->createOpportunity($manager, 'Opportunity one', $owner, 40000, 'in progress');
        $secondOppo = $this->createOpportunity($manager, 'Opportunity two', $owner, 20000, 'won');

        $closeDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $closeDate->modify('-1 day');
        $secondOppo->setCloseDate($closeDate);
        $secondOppo->setCloseRevenue(MultiCurrency::create('10000', 'USD'));

        $manager->persist($firstOppo);
        $manager->persist($secondOppo);
        $manager->flush();
    }

    private function createOpportunity(
        ObjectManager $manager,
        string $name,
        User $owner,
        int $amount,
        string $statusName
    ): Opportunity {
        $opportunity = new Opportunity();
        $opportunity->setName($name);
        $opportunity->setOwner($owner);
        $opportunity->setBudgetAmount(MultiCurrency::create($amount, 'USD'));
        $opportunity->setStatus(
            $manager->getRepository(EnumOption::class)
                ->find(
                    ExtendHelper::buildEnumOptionId(
                        Opportunity::INTERNAL_STATUS_CODE,
                        ExtendHelper::buildEnumInternalId($statusName)
                    )
                )
        );
        $opportunity->setOrganization($this->getReference(LoadOrganization::ORGANIZATION));

        return $opportunity;
    }
}
