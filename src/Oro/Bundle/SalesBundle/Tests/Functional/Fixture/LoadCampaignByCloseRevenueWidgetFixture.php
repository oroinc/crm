<?php
namespace Oro\Bundle\SalesBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\DashboardBundle\Entity\Dashboard;
use Oro\Bundle\DashboardBundle\Entity\Widget;

class LoadCampaignByCloseRevenueWidgetFixture extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager): void
    {
        /** @var Dashboard $dashboard */
        $dashboard = $this->getReference('dashboard');
        $closeRevenueWidget = new Widget();
        $closeRevenueWidget
            ->setDashboard($dashboard)
            ->setName('campaigns_by_close_revenue')
            ->setLayoutPosition([1, 1]);
        $dashboard->addWidget($closeRevenueWidget);

        if (!$this->hasReference('widget_campaigns_by_close_revenue')) {
            $this->setReference('widget_campaigns_by_close_revenue', $closeRevenueWidget);
        }

        $manager->persist($closeRevenueWidget);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadCampaignOpportunityWidgetFixture::class
        ];
    }
}
