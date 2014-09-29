<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Oro\Bundle\DashboardBundle\Migrations\Data\ORM\AbstractDashboardFixture;

class AddECommerceDashboard extends AbstractDashboardFixture implements DependentFixtureInterface
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
        $dashboard = $this->findAdminDashboardModel($manager, 'e_commerce');
        if (!$dashboard) {
            $dashboard = $this->createAdminDashboardModel($manager, 'e_commerce');
            $dashboard->setLabel($this->container->get('translator')->trans('orocrm.magento.dashboard.e_commerce'));
        }

        $dashboard->addWidget($this->createWidgetModel('average_order_amount_chart', [0, 0]))
            ->addWidget($this->createWidgetModel('new_magento_customers_chart', [1, 0]));

        $manager->flush();
    }
}
