<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\DashboardBundle\Entity\Dashboard;
use Oro\Bundle\DashboardBundle\Entity\Widget;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;

class LoadCampaignLeadsWidgetFixture extends AbstractFixture implements DependentFixtureInterface
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
        $this->createCampaign($manager);
        $this->createOrphanCampaign($manager);
        $this->createLeads($manager);

        $dashboard = new Dashboard();
        $dashboard->setName('dashboard');
        $campaignLeadsWidget = new Widget();
        $campaignLeadsWidget->setDashboard($dashboard);
        $campaignLeadsWidget->setName('campaigns_leads');
        $campaignLeadsWidget->setLayoutPosition([1, 1]);
        $dashboard->addWidget($campaignLeadsWidget);
        if (!$this->hasReference('widget_campaigns_leads')) {
            $this->setReference('widget_campaigns_leads', $campaignLeadsWidget);
        }
        $manager->persist($dashboard);
        $manager->flush();
    }

    private function createLeads(ObjectManager $manager): void
    {
        $createdAt = new \DateTime('2016-12-28 12:03:10', new \DateTimeZone('UTC'));
        for ($i = 1; $i < 4; $i++) {
            $this->createLead($manager, $createdAt, $i);
            $createdAt->add(new \DateInterval('P1D'));
        }

        //insert one lead for previous months
        $createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->createLead($manager, $createdAt, ++$i);
    }

    private function createLead(ObjectManager $manager, \DateTime $createdAt, int $id): void
    {
        $lead = new Lead();
        $lead->setName('name ' . $id);
        $lead->setOrganization($this->getReference(LoadOrganization::ORGANIZATION));
        $lead->setCampaign($this->getReference('default_campaign'));
        $manager->persist($lead);
        $lead->setCreatedAt($createdAt);
        $manager->flush();
    }

    private function createCampaign(ObjectManager $manager): void
    {
        $campaign = new Campaign();
        $campaign->setName('Campaign');
        $campaign->setCode('cmp');
        $campaign->setOrganization($this->getReference(LoadOrganization::ORGANIZATION));
        $campaign->setReportPeriod(Campaign::PERIOD_MONTHLY);
        $manager->persist($campaign);
        $manager->flush();
        $this->setReference('default_campaign', $campaign);
    }

    private function createOrphanCampaign(ObjectManager $manager): void
    {
        $campaign = new Campaign();
        $campaign->setName('OrphanCampaign');
        $campaign->setCode('ocmp');
        $campaign->setOrganization($this->getReference(LoadOrganization::ORGANIZATION));
        $campaign->setReportPeriod(Campaign::PERIOD_HOURLY);
        $manager->persist($campaign);
        $manager->flush();
        $this->setReference('orphan_campaign', $campaign);
    }
}
