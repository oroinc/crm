<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\DashboardBundle\Entity\Dashboard;
use Oro\Bundle\DashboardBundle\Entity\Widget;

class LoadLeadsListWidgetFixture extends AbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $dashboard = new Dashboard();
        $dashboard->setName('dashboard');

        $leadStaticsWidget = new Widget();
        $leadStaticsWidget
            ->setDashboard($dashboard)
            ->setName('leads_list')
            ->setLayoutPosition([1, 1]);

        $dashboard->addWidget($leadStaticsWidget);

        if (!$this->hasReference('widget_leads_list')) {
            $this->setReference('widget_leads_list', $leadStaticsWidget);
        }

        $manager->persist($dashboard);
        $manager->flush();
    }
}
