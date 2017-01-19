<?php
namespace Oro\Bundle\SalesBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\CurrencyBundle\Entity\MultiCurrency;
use Oro\Bundle\DashboardBundle\Entity\Dashboard;
use Oro\Bundle\DashboardBundle\Entity\Widget;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

class LoadCampaignByCloseRevenueWidgetFixture extends AbstractFixture
{
    /**
     * @var Organization
     */
    protected $organization;
    /**
     * @var ObjectManager
     */
    protected $em;

    private $opportunityCount = 0;

    protected function createLead()
    {
        $lead = new Lead();
        $lead->setName('Lead name');
        $lead->setOrganization($this->organization);
        $lead->setCampaign($this->getReference('default_campaign'));
        $this->em->persist($lead);
        $this->em->flush();
        $this->setReference('default_lead', $lead);
    }

    protected function createOpportunity($createdAt, $status)
    {
        $className = ExtendHelper::buildEnumValueClassName(Opportunity::INTERNAL_STATUS_CODE);
        $opportunityStatus = $this->em->getRepository($className)->find(ExtendHelper::buildEnumValueId($status));

        $opportunity = new Opportunity();
        $opportunity->setName(sprintf('Test Opportunity #%d', ++$this->opportunityCount));
        $opportunity->setStatus($opportunityStatus);
        $opportunity->setLead($this->getReference('default_lead'));
        $opportunity->setCloseRevenue(MultiCurrency::create(100, 'USD'));
        $opportunity->setOrganization($this->organization);
        $this->em->persist($opportunity);

        $opportunity->setCreatedAt($createdAt);
        $this->em->flush();
    }

    protected function createCampaign()
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

    protected function createOpportunities()
    {
        $createdAt = new \DateTime('2016-12-28 12:03:10', new \DateTimeZone('UTC'));
        // Every opportunity has value of $100
        $this->createOpportunity($createdAt, 'won');
        $this->createOpportunity($createdAt, 'in_progress');
        $this->createOpportunity($createdAt, 'lost');

        $createdAt->add(new \DateInterval('P1D'));
        $this->createOpportunity($createdAt, 'won');
    }

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        $this->organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();
        $this->em = $manager;
        $this->createCampaign();
        $this->createLead();
        $this->createOpportunities();
        $dashboard = new Dashboard();
        $dashboard->setName('dashboard');
        $campaignLeadsWidget = new Widget();
        $campaignLeadsWidget
            ->setDashboard($dashboard)
            ->setName('campaigns_by_close_revenue')
            ->setLayoutPosition([1, 1]);
        $dashboard->addWidget($campaignLeadsWidget);
        if (!$this->hasReference('widget_campaigns_by_close_revenue')) {
            $this->setReference('widget_campaigns_by_close_revenue', $campaignLeadsWidget);
        }
        $manager->persist($dashboard);
        $manager->flush();
    }
}
