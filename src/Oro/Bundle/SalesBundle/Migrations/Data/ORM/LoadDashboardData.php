<?php

namespace Oro\Bundle\SalesBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\DashboardBundle\Migrations\Data\ORM\AbstractDashboardFixture;
use Oro\Bundle\DashboardBundle\Migrations\Data\ORM\LoadDashboardData as DependedLoadDashboardData;

/**
 * Adds widgets to "main" dashboard.
 */
class LoadDashboardData extends AbstractDashboardFixture implements DependentFixtureInterface
{
    #[\Override]
    public function getDependencies(): array
    {
        return [DependedLoadDashboardData::class];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $mainDashboard = $this->findAdminDashboardModel($manager, 'main');
        if ($mainDashboard) {
            $mainDashboard
                ->addWidget($this->createWidgetModel('opportunities_by_lead_source_chart', [1, 80]))
                ->addWidget($this->createWidgetModel('opportunities_by_state', [0, 90]))
                ->addWidget($this->createWidgetModel('campaigns_leads', [1, 130]))
                ->addWidget($this->createWidgetModel('campaigns_opportunity', [0, 150]))
                ->addWidget($this->createWidgetModel('campaigns_by_close_revenue', [1, 150]));
            $manager->flush();
        }
    }
}
