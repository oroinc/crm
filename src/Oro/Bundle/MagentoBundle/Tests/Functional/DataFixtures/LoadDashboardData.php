<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DashboardBundle\Entity\Dashboard;
use Oro\Bundle\DashboardBundle\Entity\Widget;

class LoadDashboardData extends AbstractFixture
{

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $dashboard = new Dashboard();
        $dashboard
            ->setName('TestWidgets')
            ->setLabel('TestWidgets')
            ->setIsDefault(false);

        $manager->persist($dashboard);

        $averageOrderAmountChart = new Widget();
        $averageOrderAmountChart
            ->setDashboard($dashboard)
            ->setName('average_order_amount_chart')
            ->setLayoutPosition([1, 0]);

        $newMagentoCustomersChart = new Widget();
        $newMagentoCustomersChart
            ->setDashboard($dashboard)
            ->setName('new_magento_customers_chart')
            ->setLayoutPosition([0, 1]);

        $manager->persist($dashboard);
        $manager->persist($averageOrderAmountChart);
        $manager->persist($newMagentoCustomersChart);
        $manager->flush();
    }
}
