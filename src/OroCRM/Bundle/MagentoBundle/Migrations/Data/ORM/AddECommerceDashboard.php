<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Oro\Bundle\DashboardBundle\Migrations\Data\ORM\AbstractDashboardFixture;
use Oro\Bundle\DashboardBundle\Model\WidgetModel;
use Oro\Bundle\MigrationBundle\Fixture\VersionedFixtureInterface;

class AddECommerceDashboard extends AbstractDashboardFixture implements
    DependentFixtureInterface,
    VersionedFixtureInterface
{
    /** @var array */
    protected $widgets = [
        [
            'name' => 'average_order_amount_chart',
            'layout' => [0, 0],
        ],
        [
            'name' => 'new_magento_customers_chart',
            'layout' => [1, 0],
        ],
        [
            'name' => 'revenue_over_time_chart',
            'layout' => [0, 1],
        ],
        [
            'name' => 'orders_over_time_chart',
            'layout' => [0, 1],
        ],
        [
            'name' => 'purchase_chart',
            'layout' => [0, 1],
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '1.0';
    }

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

        foreach ($this->widgets as $widgetData) {
            $widgets = $dashboard->getWidgets()->filter(function (WidgetModel $widget) use ($widgetData) {
                return $widget->getName() === $widgetData['name'];
            });

            if (count($widgets)) {
                continue;
            }

            $dashboard->addWidget($this->createWidgetModel($widgetData['name'], $widgetData['layout']));
        }

        $manager->flush();
    }
}
