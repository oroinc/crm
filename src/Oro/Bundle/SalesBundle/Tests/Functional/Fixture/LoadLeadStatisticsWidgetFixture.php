<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\DashboardBundle\Entity\Dashboard;
use Oro\Bundle\DashboardBundle\Entity\Widget;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SalesBundle\Entity\Lead;

class LoadLeadStatisticsWidgetFixture extends AbstractFixture
{
    /**
     * @var Organization
     */
    protected $organization;

    /**
     * @var ObjectManager
     */
    protected $em;

    protected function createLead()
    {
        $className = ExtendHelper::buildEnumValueClassName(Lead::INTERNAL_STATUS_CODE);
        $newStatus = $this->em->getRepository($className)->find(ExtendHelper::buildEnumValueId('new'));

        $lead = new Lead();
        $lead->setName('Lead name');
        $lead->setFirstName('fname');
        $lead->setLastName('lname');
        $lead->setStatus($newStatus);
        $lead->setOrganization($this->organization);

        $this->em->persist($lead);
        $this->em->flush();
    }

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        $this->organization = $manager->getRepository(Organization::class)->getFirst();
        $this->em = $manager;
        $this->createLead();

        $dashboard = new Dashboard();
        $dashboard->setName('dashboard');

        $leadStaticsWidget = new Widget();
        $leadStaticsWidget
            ->setDashboard($dashboard)
            ->setName('lead_statistics')
            ->setLayoutPosition([1, 1]);

        $dashboard->addWidget($leadStaticsWidget);

        if (!$this->hasReference('widget_lead_statistics')) {
            $this->setReference('widget_lead_statistics', $leadStaticsWidget);
        }

        $manager->persist($dashboard);
        $manager->flush();
    }
}
