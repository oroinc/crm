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

    /**
     * @param string    $name
     * @param Campaign  $campaign
     * @param string    $referenceName
     *
     * @return Lead
     */
    protected function createLead(
        $name,
        Campaign $campaign,
        $referenceName = null
    ) {
        $lead = new Lead();
        $lead->setName($name);
        $lead->setOrganization($this->organization);
        $lead->setCampaign($campaign);
        $this->em->persist($lead);
        $this->em->flush();

        ($referenceName === null) ?: $this->setReference($referenceName, $lead);

        return $lead;
    }

    /**
     * @param \DateTime $createdAt
     * @param string    $status
     * @param Lead      $lead
     * @param int       $closeRevenue
     */
    protected function createOpportunity(
        $createdAt,
        $status,
        Lead $lead,
        $closeRevenue = null
    ) {
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

    /**
     * @param string $name
     * @param string $code
     * @param string $reference
     *
     * @return Campaign
     */
    protected function createCampaign($name, $code, $reference = null)
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

    protected function createOpportunities()
    {
        $createdAt = new \DateTime('2016-12-28 12:03:10', new \DateTimeZone('UTC'));
        $defaultCampaign = $this->createCampaign('Default campaing', 'cmt');
        $anotherCampaign = $this->createCampaign('Another campaing', 'test');
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
    public function load(ObjectManager $manager)
    {
        $this->organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();
        $this->em = $manager;
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
