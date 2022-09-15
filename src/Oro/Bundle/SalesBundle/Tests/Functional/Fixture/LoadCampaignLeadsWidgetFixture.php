<?php
namespace Oro\Bundle\SalesBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\DashboardBundle\Entity\Dashboard;
use Oro\Bundle\DashboardBundle\Entity\Widget;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SalesBundle\Entity\Lead;

class LoadCampaignLeadsWidgetFixture extends AbstractFixture
{
    protected Organization $organization;

    protected ObjectManager $em;

    protected function createLeads()
    {
        $createdAt = new \DateTime('2016-12-28 12:03:10', new \DateTimeZone('UTC'));
        for ($i = 1; $i < 4; $i++) {
            $this->createLead($createdAt, $i);
            $createdAt->add(new \DateInterval('P1D'));
        }

        //insert one lead for previous months
        $createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->createLead($createdAt, ++$i);
    }

    public function createLead($createdAt, $id): void
    {
        $lead = new Lead();
        $lead->setName('name '.$id);
        $lead->setOrganization($this->organization);
        $lead->setCampaign($this->getReference('default_campaign'));
        $this->em->persist($lead);
        $lead->setCreatedAt($createdAt);
        $this->em->flush();
    }

    public function createCampaign(): void
    {
        $campaign = new Campaign();
        $campaign->setName('Campaign');
        $campaign->setCode('cmp');
        $campaign->setOrganization($this->organization);
        $campaign->setReportPeriod(Campaign::PERIOD_MONTHLY);
        $this->em->persist($campaign);
        $this->em->flush();
        $this->setReference('default_campaign', $campaign);
    }

    public function createOrphanCampaign(): void
    {
        $campaign = new Campaign();
        $campaign->setName('OrphanCampaign');
        $campaign->setCode('ocmp');
        $campaign->setOrganization($this->organization);
        $campaign->setReportPeriod(Campaign::PERIOD_HOURLY);
        $this->em->persist($campaign);
        $this->em->flush();
        $this->setReference('orphan_campaign', $campaign);
    }

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        $this->organization = $manager->getRepository(Organization::class)->getFirst();
        $this->em = $manager;
        $this->createCampaign();
        $this->createOrphanCampaign();
        $this->createLeads();
        $dashboard = new Dashboard();
        $dashboard->setName('dashboard');
        $campaignLeadsWidget = new Widget();
        $campaignLeadsWidget
            ->setDashboard($dashboard)
            ->setName('campaigns_leads')
            ->setLayoutPosition([1, 1]);
        $dashboard->addWidget($campaignLeadsWidget);
        if (!$this->hasReference('widget_campaigns_leads')) {
            $this->setReference('widget_campaigns_leads', $campaignLeadsWidget);
        }
        $manager->persist($dashboard);
        $manager->flush();
    }
}
