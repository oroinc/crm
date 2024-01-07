<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\CurrencyBundle\Entity\MultiCurrency;
use Oro\Bundle\DashboardBundle\Entity\Dashboard;
use Oro\Bundle\DashboardBundle\Entity\Widget;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;

class LoadCampaignOpportunityWidgetFixture extends AbstractFixture implements DependentFixtureInterface
{
    private int $opportunityCount = 0;

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
        $campaignOprWidget = new Widget();
        $campaignOprWidget->setDashboard($dashboard);
        $campaignOprWidget->setName('campaigns_opportunity');
        $campaignOprWidget->setLayoutPosition([1, 1]);
        $dashboard->addWidget($campaignOprWidget);
        $this->setReference('dashboard', $dashboard);
        if (!$this->hasReference('widget_campaigns_opportunity')) {
            $this->setReference('widget_campaigns_opportunity', $campaignOprWidget);
        }
        $manager->persist($dashboard);
        $manager->flush();
    }

    private function createOpportunities(ObjectManager $manager): void
    {
        $createdAt = new \DateTime('2016-12-28 12:03:10', new \DateTimeZone('UTC'));
        $defaultCampaign = $this->createCampaign($manager, 'Default campaign', 'cmt');
        $anotherCampaign = $this->createCampaign($manager, 'Another campaign', 'test');
        $defaultLead = $this->createLead($manager, 'Default Lead', $defaultCampaign);
        $anotherLead = $this->createLead($manager, 'Another Lead', $anotherCampaign);

        // Every opportunity has value of $100
        $this->createOpportunity($manager, $createdAt, 'won', $defaultLead, 100);
        $this->createOpportunity($manager, $createdAt, 'in_progress', $defaultLead, 100);
        $this->createOpportunity($manager, $createdAt, 'lost', $defaultLead, 100);

        //This opportunity without close revenue
        $this->createOpportunity($manager, $createdAt, 'won', $anotherLead);

        $createdAt->add(new \DateInterval('P1D'));
        $this->createOpportunity($manager, $createdAt, 'won', $defaultLead, 100);
    }

    private function createCampaign(ObjectManager $manager, string $name, string $code): Campaign
    {
        $campaign = new Campaign();
        $campaign->setName($name);
        $campaign->setCode($code);
        $campaign->setOrganization($this->getReference(LoadOrganization::ORGANIZATION));
        $campaign->setReportPeriod(Campaign::PERIOD_MONTHLY);
        $manager->persist($campaign);
        $manager->flush();

        return $campaign;
    }

    private function createLead(ObjectManager $manager, string $name, Campaign $campaign): Lead
    {
        $lead = new Lead();
        $lead->setName($name);
        $lead->setOrganization($this->getReference(LoadOrganization::ORGANIZATION));
        $lead->setCampaign($campaign);
        $manager->persist($lead);
        $manager->flush();

        return $lead;
    }

    private function createOpportunity(
        ObjectManager $manager,
        \DateTime $createdAt,
        string $status,
        Lead $lead,
        ?int $closeRevenue = null
    ): void {
        $opportunity = new Opportunity();
        $opportunity->setName(sprintf('Test Opportunity #%d', ++$this->opportunityCount));
        $opportunity->setStatus(
            $manager->getRepository(ExtendHelper::buildEnumValueClassName(Opportunity::INTERNAL_STATUS_CODE))
                ->find(ExtendHelper::buildEnumValueId($status))
        );
        $opportunity->setLead($lead);
        if (null !== $closeRevenue) {
            $opportunity->setCloseRevenue(MultiCurrency::create($closeRevenue, 'USD'));
        }
        $opportunity->setOrganization($this->getReference(LoadOrganization::ORGANIZATION));
        $manager->persist($opportunity);
        $opportunity->setCreatedAt($createdAt);
        $manager->flush();
    }
}
