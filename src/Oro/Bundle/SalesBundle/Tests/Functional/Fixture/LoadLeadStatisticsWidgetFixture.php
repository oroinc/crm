<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\DashboardBundle\Entity\Dashboard;
use Oro\Bundle\DashboardBundle\Entity\Widget;

class LoadLeadStatisticsWidgetFixture extends AbstractFixture
{
    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        $dasboard = new Dashboard();
        $dasboard->setName('dashboard');

        $leadStaticsWidget = new Widget();
        $leadStaticsWidget
            ->setDashboard($dasboard)
            ->setName('lead_statistics')
            ->setLayoutPosition([1, 1]);

        $dasboard->addWidget($leadStaticsWidget);

        if (!$this->hasReference('widget_lead_statistics')) {
            $this->setReference('widget_lead_statistics', $leadStaticsWidget);
        }

        $manager->persist($dasboard);
        $manager->flush();
    }
}
