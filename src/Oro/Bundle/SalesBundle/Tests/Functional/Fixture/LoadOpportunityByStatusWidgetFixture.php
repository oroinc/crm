<?php
namespace Oro\Bundle\SalesBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\DashboardBundle\Entity\Dashboard;
use Oro\Bundle\DashboardBundle\Entity\Widget;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

class LoadOpportunityByStatusWidgetFixture extends AbstractFixture
{
    /**
     * @var Organization
     */
    protected $organization;
    /**
     * @var ObjectManager
     */
    protected $em;
    protected function createOpportunities()
    {
        $createdAt = new \DateTime('2016-12-28 12:03:10', new \DateTimeZone('UTC'));
        for ($i = 1; $i < 4; $i++) {
            $this->createOpportunity($createdAt, $i);
            $createdAt->add(new \DateInterval('P1D'));
        }

        //insert one opportunity for previous months
        $createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->createOpportunity($createdAt, ++$i);
    }

    public function createOpportunity($createdAt, $id)
    {
        $className = ExtendHelper::buildEnumValueClassName(Opportunity::INTERNAL_STATUS_CODE);
        $openStatus = $this->em->getRepository($className)->find(ExtendHelper::buildEnumValueId('in_progress'));
        $opportunity = new Opportunity();
        $opportunity->setName('name '.$id);
        $opportunity->setStatus($openStatus);
        $opportunity->setOrganization($this->organization);
        $this->em->persist($opportunity);
        $opportunity->setCreatedAt($createdAt);
        $this->em->flush();
    }
    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        $this->organization = $manager->getRepository(Organization::class)->getFirst();
        $this->em = $manager;
        $this->createOpportunities();
        $dashboard = new Dashboard();
        $dashboard->setName('dashboard');
        $opportunityByStatusWidget = new Widget();
        $opportunityByStatusWidget
            ->setDashboard($dashboard)
            ->setName('opportunities_by_state')
            ->setLayoutPosition([1, 1]);
        $dashboard->addWidget($opportunityByStatusWidget);
        if (!$this->hasReference('widget_opportunity_by_status')) {
            $this->setReference('widget_opportunity_by_status', $opportunityByStatusWidget);
        }
        $manager->persist($dashboard);
        $manager->flush();
    }
}
