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

class LoadForecastWidgetFixtures extends AbstractFixture
{
    private $organization;

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        $this->organization = $manager->getRepository(Organization::class)->getFirst();

        $this->addWidget($manager);
        $this->createOpportunity($manager);
    }

    private function addWidget(ObjectManager $manager)
    {
        $dashboard = new Dashboard();
        $dashboard->setName('Test dashboard');

        $leadStaticsWidget = new Widget();
        $leadStaticsWidget
            ->setDashboard($dashboard)
            ->setName('forecast_of_opportunities')
            ->setLayoutPosition([1, 1]);

        $dashboard->addWidget($leadStaticsWidget);

        if (!$this->hasReference('widget_forecast')) {
            $this->setReference('widget_forecast', $leadStaticsWidget);
        }
        $manager->persist($dashboard);
        $manager->flush();
    }

    private function createOpportunity(ObjectManager $manager)
    {
        $today = new \DateTime('now', new \DateTimeZone('UTC'));
        $firstOfCurrentMonth = new \DateTime('first day of this month midnight', new \DateTimeZone('UTC'));

        $opportunityList = [
            [
                'status' => 'in_progress',
                'close_date' => null,
                'probability' => 0.1, //percents
                'budget_amount' => 100, //USD
            ],
            [
                'status' => 'in_progress',
                'close_date' => $today,
                'probability' => 0.1, //percents
                'budget_amount' => 100, //USD
            ],
            [
                'status' => 'in_progress',
                'close_date' => $firstOfCurrentMonth,
                'probability' => 1, //percents
                'budget_amount' => 100, //USD
            ],
        ];

        foreach ($opportunityList as $opportunityName => $opportunityData) {
            $opportunity = new Opportunity();
            $opportunity->setName(sprintf('test_opportunity_%s', $opportunityName));
            $budgetAmount = MultiCurrency::create($opportunityData['budget_amount'], 'USD');
            $opportunity->setBudgetAmount($budgetAmount);

            $opportunity->setProbability($opportunityData['probability']);
            $opportunity->setOrganization($this->organization);
            $opportunity->setCloseDate($opportunityData['close_date']);

            $enumClass = ExtendHelper::buildEnumValueClassName(Opportunity::INTERNAL_STATUS_CODE);
            $opportunity->setStatus($manager->getReference($enumClass, $opportunityData['status']));

            $manager->persist($opportunity);
        }

        $manager->flush();
    }
}
