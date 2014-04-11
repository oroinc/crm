<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Oro\Bundle\DashboardBundle\Migrations\Data\ORM\AbstractDashboardFixture;

class LoadDashboardData extends AbstractDashboardFixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return ['Oro\Bundle\DashboardBundle\Migrations\Data\ORM\LoadDashboardData'];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $mainDashboard = $this->findAdminDashboard($manager, 'main');

        if (!$mainDashboard) {
            $this->addNewDashboardWidget($manager, $mainDashboard, 'opportunities_by_lead_source_chart')
                ->setLayoutPosition([1, 80]);
            $this->addNewDashboardWidget($manager, $mainDashboard, 'opportunities_by_state')
                ->setLayoutPosition([0, 90]);
            $this->addNewDashboardWidget($manager, $mainDashboard, 'my_sales_flow_b2b_chart')
                ->setLayoutPosition([1, 120]);

            $manager->flush();
        }
    }
}
