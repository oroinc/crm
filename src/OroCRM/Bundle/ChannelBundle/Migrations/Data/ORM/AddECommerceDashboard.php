<?php

namespace OroCRM\Bundle\ChannelBundle\Migrations\Data\ORM;

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
            $dashboard->setLabel(
                $this->container->get('translator')->trans('orocrm.channel.dashboard.e_commerce.label')
            );
        }

        $dashboard->addWidget($this->createWidgetModel('average_lifetime_sales_chart', [0, 1]));
        $manager->flush();
    }
}
