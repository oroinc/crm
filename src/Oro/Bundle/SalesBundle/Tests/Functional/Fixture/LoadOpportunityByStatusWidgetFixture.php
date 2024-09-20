<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\DashboardBundle\Entity\Dashboard;
use Oro\Bundle\DashboardBundle\Entity\Widget;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOption;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;

class LoadOpportunityByStatusWidgetFixture extends AbstractFixture implements DependentFixtureInterface
{
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
        $this->createOpportunities($manager);

        $dashboard = new Dashboard();
        $dashboard->setName('dashboard');
        $opportunityByStatusWidget = new Widget();
        $opportunityByStatusWidget->setDashboard($dashboard);
        $opportunityByStatusWidget->setName('opportunities_by_state');
        $opportunityByStatusWidget->setLayoutPosition([1, 1]);
        $dashboard->addWidget($opportunityByStatusWidget);
        if (!$this->hasReference('widget_opportunity_by_status')) {
            $this->setReference('widget_opportunity_by_status', $opportunityByStatusWidget);
        }
        $manager->persist($dashboard);
        $manager->flush();
    }

    private function createOpportunities(ObjectManager $manager): void
    {
        $createdAt = new \DateTime('2016-12-28 12:03:10', new \DateTimeZone('UTC'));
        for ($i = 1; $i < 4; $i++) {
            $this->createOpportunity($manager, $createdAt, $i);
            $createdAt->add(new \DateInterval('P1D'));
        }

        //insert one opportunity for previous months
        $createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->createOpportunity($manager, $createdAt, ++$i);
    }

    private function createOpportunity(ObjectManager $manager, \DateTime $createdAt, int $id): void
    {
        $opportunity = new Opportunity();
        $opportunity->setName('name ' . $id);
        $opportunity->setStatus(
            $manager->getRepository(EnumOption::class)
                ->find(
                    ExtendHelper::buildEnumOptionId(
                        Opportunity::INTERNAL_STATUS_CODE,
                        ExtendHelper::buildEnumInternalId('in_progress')
                    )
                )
        );
        $opportunity->setOrganization($this->getReference(LoadOrganization::ORGANIZATION));
        $manager->persist($opportunity);
        $opportunity->setCreatedAt($createdAt);
        $manager->flush();
    }
}
