<?php
namespace Oro\Bundle\SalesBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\CurrencyBundle\Entity\MultiCurrency;
use Oro\Bundle\DashboardBundle\Entity\Dashboard;
use Oro\Bundle\DashboardBundle\Entity\Widget;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

class LoadCampaignOpportunityWidgetFixture extends AbstractFixture
{
    protected Organization $organization;

    protected ObjectManager $em;

    private int $opportunityCount = 0;

    protected function createLead(
        string $name,
        Campaign $campaign,
        ?string $referenceName = null
    ): Lead {
        $lead = new Lead();
        $lead->setName($name);
        $lead->setOrganization($this->organization);
        $lead->setCampaign($campaign);
        $this->em->persist($lead);
        $this->em->flush();

        ($referenceName === null) ?: $this->setReference($referenceName, $lead);

        return $lead;
    }

    protected function createOpportunity(
        \DateTime $createdAt,
        string $status,
        Lead $lead,
        ?int $closeRevenue = null
    ): void {
        $className = ExtendHelper::buildEnumValueClassName(Opportunity::INTERNAL_STATUS_CODE);
        $opportunityStatus = $this->em->getRepository($className)->find(ExtendHelper::buildEnumValueId($status));
        $opportunity = new Opportunity();
        $opportunity->setName(sprintf('Test Opportunity #%d', ++$this->opportunityCount));
        $opportunity->setStatus($opportunityStatus);
        $opportunity->setLead($lead);

        ($closeRevenue === null) ?: $opportunity->setCloseRevenue(MultiCurrency::create($closeRevenue, 'USD'));

        $opportunity->setOrganization($this->organization);
        $this->em->persist($opportunity);

        $opportunity->setCreatedAt($createdAt);
        $this->em->flush();
    }

    protected function createCampaign(string $name, string $code, string $reference = null): Campaign
    {
        $campaign = new Campaign();
        $campaign->setName($name);
        $campaign->setCode($code);
        $campaign->setOrganization($this->organization);
        $campaign->setReportPeriod(Campaign::PERIOD_MONTHLY);
        $this->em->persist($campaign);
        $this->em->flush();
        ($reference === null) ?: $this->setReference($reference, $campaign);

        return $campaign;
    }

    protected function createOpportunities(): void
    {
        $createdAt = new \DateTime('2016-12-28 12:03:10', new \DateTimeZone('UTC'));
        $defaultCampaign = $this->createCampaign('Default campaign', 'cmt');
        $anotherCampaign = $this->createCampaign('Another campaign', 'test');
        $defaultLead = $this->createLead('Default Lead', $defaultCampaign);
        $anotherLead = $this->createLead('Another Lead', $anotherCampaign);

        // Every opportunity has value of $100
        $this->createOpportunity($createdAt, 'won', $defaultLead, 100);
        $this->createOpportunity($createdAt, 'in_progress', $defaultLead, 100);
        $this->createOpportunity($createdAt, 'lost', $defaultLead, 100);

        //This opportunity without close revenue
        $this->createOpportunity($createdAt, 'won', $anotherLead);

        $createdAt->add(new \DateInterval('P1D'));
        $this->createOpportunity($createdAt, 'won', $defaultLead, 100);
    }

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager): void
    {
        $this->organization = $manager->getRepository(Organization::class)->getFirst();
        $this->em = $manager;
        $this->createOpportunities();
        $dashboard = new Dashboard();
        $dashboard->setName('dashboard');
        $campaignOprWidget = new Widget();
        $campaignOprWidget
            ->setDashboard($dashboard)
            ->setName('campaigns_opportunity')
            ->setLayoutPosition([1, 1]);
        $dashboard->addWidget($campaignOprWidget);

        $this->setReference('dashboard', $dashboard);
        if (!$this->hasReference('widget_campaigns_opportunity')) {
            $this->setReference('widget_campaigns_opportunity', $campaignOprWidget);
        }

        $manager->persist($dashboard);
        $manager->flush();
    }
}
