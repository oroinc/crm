<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\DashboardBundle\Entity\Dashboard;
use Oro\Bundle\DashboardBundle\Entity\Widget;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;

class LoadLeadStatisticsWidgetFixture extends AbstractFixture implements DependentFixtureInterface
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
        $this->createLead($manager);

        $dashboard = new Dashboard();
        $dashboard->setName('dashboard');
        $leadStaticsWidget = new Widget();
        $leadStaticsWidget->setDashboard($dashboard);
        $leadStaticsWidget->setName('lead_statistics');
        $leadStaticsWidget->setLayoutPosition([1, 1]);
        $dashboard->addWidget($leadStaticsWidget);
        if (!$this->hasReference('widget_lead_statistics')) {
            $this->setReference('widget_lead_statistics', $leadStaticsWidget);
        }
        $manager->persist($dashboard);
        $manager->flush();
    }

    private function createLead(ObjectManager $manager): void
    {
        $lead = new Lead();
        $lead->setName('Lead name');
        $lead->setFirstName('fname');
        $lead->setLastName('lname');
        $lead->setStatus(
            $manager->getRepository(ExtendHelper::buildEnumValueClassName(Lead::INTERNAL_STATUS_CODE))
                ->find(ExtendHelper::buildEnumValueId('new'))
        );
        $lead->setOrganization($this->getReference(LoadOrganization::ORGANIZATION));

        $manager->persist($lead);
        $manager->flush();
    }
}
