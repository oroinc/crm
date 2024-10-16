<?php

namespace Oro\Bundle\AccountBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\DashboardBundle\Migrations\Data\ORM\AbstractDashboardFixture;
use Oro\Bundle\DashboardBundle\Migrations\Data\ORM\LoadDashboardData as LoadMainDashboardData;

/**
 * Adds "my_accounts_activity" widget to "main" dashboard.
 */
class LoadDashboardData extends AbstractDashboardFixture implements DependentFixtureInterface
{
    #[\Override]
    public function getDependencies(): array
    {
        return [LoadMainDashboardData::class];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $mainDashboard = $this->findAdminDashboardModel($manager, 'main');
        if ($mainDashboard) {
            $mainDashboard->addWidget($this->createWidgetModel('my_accounts_activity', [0, 40]));
            $manager->flush();
        }
    }
}
