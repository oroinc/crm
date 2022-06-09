<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CurrencyBundle\Entity\MultiCurrency;
use Oro\Bundle\DashboardBundle\Entity\Dashboard;
use Oro\Bundle\DashboardBundle\Entity\Widget;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\UserBundle\Entity\User;

class LoadOpportunityStatisticsWidgetFixture extends AbstractFixture
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
        $owner = new User;
        $owner->setId(18);
        $owner->setUsername('owner');
        $owner->setEmail('owner@example.com');
        $owner->setPassword('secrecy');

        $this->em->persist($owner);

        $firstOppo  = $this->createOpportunity('Opportunity one', $owner, 40000, 'in progress', $this->organization);
        /** @var Opportunity $secondOppo */
        $secondOppo = $this->createOpportunity('Opportunity two', $owner, 20000, 'won', $this->organization);

        $closeDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $closeDate->modify('-1 day');
        $secondOppo->setCloseDate($closeDate);
        $secondOppo->setCloseRevenue(MultiCurrency::create('10000', 'USD'));

        $this->em->persist($firstOppo);
        $this->em->persist($secondOppo);
        $this->em->flush();
    }

    protected function createOpportunity($name, $owner, $amount, $statusName, $organization)
    {
        $opportunity = new Opportunity();
        $opportunity->setName($name);
        $opportunity->setOwner($owner);

        $budgetAmount = MultiCurrency::create($amount, 'USD');
        $opportunity->setBudgetAmount($budgetAmount);

        $className = ExtendHelper::buildEnumValueClassName(Opportunity::INTERNAL_STATUS_CODE);
        $statusId = $this->em->getRepository($className)->find(ExtendHelper::buildEnumValueId($statusName));

        $opportunity->setStatus($statusId);
        $opportunity->setOrganization($organization);

        return $opportunity;
    }

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        $this->organization = $manager->getRepository(Organization::class)->getFirst();
        $this->em = $manager;
        $this->createOpportunities($manager);

        $dashboard = new Dashboard();
        $dashboard->setName('dashboard');

        $opportunityStaticsWidget = new Widget();
        $opportunityStaticsWidget
            ->setDashboard($dashboard)
            ->setName('opportunity_statistics')
            ->setLayoutPosition([1, 1]);

        $dashboard->addWidget($opportunityStaticsWidget);

        if (!$this->hasReference('widget_opportunity_statistics')) {
            $this->setReference('widget_opportunity_statistics', $opportunityStaticsWidget);
        }

        $manager->persist($dashboard);
        $manager->flush();
    }
}
