<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CurrencyBundle\Entity\MultiCurrency;
use Oro\Bundle\DashboardBundle\Entity\Dashboard;
use Oro\Bundle\DashboardBundle\Entity\Widget;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;

class LoadForecastWidgetFixtures extends AbstractFixture implements DependentFixtureInterface
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
        $this->addWidget($manager);
        $this->createOpportunity($manager);
    }

    private function addWidget(ObjectManager $manager): void
    {
        $dashboard = new Dashboard();
        $dashboard->setName('Test dashboard');
        $leadStaticsWidget = new Widget();
        $leadStaticsWidget->setDashboard($dashboard);
        $leadStaticsWidget->setName('forecast_of_opportunities');
        $leadStaticsWidget->setLayoutPosition([1, 1]);
        $dashboard->addWidget($leadStaticsWidget);
        if (!$this->hasReference('widget_forecast')) {
            $this->setReference('widget_forecast', $leadStaticsWidget);
        }
        $manager->persist($dashboard);
        $manager->flush();
    }

    private function createOpportunity(ObjectManager $manager): void
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
        foreach ($opportunityList as $i => $opportunityData) {
            $opportunity = new Opportunity();
            $opportunity->setName(sprintf('test_opportunity_%s', $i));
            $opportunity->setBudgetAmount(MultiCurrency::create($opportunityData['budget_amount'], 'USD'));
            $opportunity->setProbability($opportunityData['probability']);
            $opportunity->setOrganization($this->getReference(LoadOrganization::ORGANIZATION));
            $opportunity->setCloseDate($opportunityData['close_date']);
            $opportunity->setStatus($manager->getReference(
                ExtendHelper::buildEnumValueClassName(Opportunity::INTERNAL_STATUS_CODE),
                $opportunityData['status']
            ));
            $manager->persist($opportunity);
        }
        $manager->flush();
    }
}
