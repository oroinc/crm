<?php

namespace Oro\Bundle\SalesBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\DashboardBundle\Entity\Widget;

/**
 * Split business_sales_channel_statistics widget into lead_statistics and opportunity_statistics widgets
 */
class SplitBusinessChannelStatisticsWidget extends AbstractFixture
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        /** @var Widget|null $oldWidget */
        $oldWidget = $manager->getRepository(Widget::class)
            ->findOneBy(['name' => 'business_sales_channel_statistics']);
        if (!$oldWidget) {
            return;
        }

        $options = $oldWidget->getOptions();
        $dashboard = $oldWidget->getDashboard();
        $position = $oldWidget->getLayoutPosition();

        $leadStatsWidget = new Widget();
        $leadStatsWidget->setName('lead_statistics');
        $leadStatsWidget->setDashboard($dashboard);
        $leadStatsWidget->setLayoutPosition($position);

        $opportunityStatsWidget = new Widget();
        $opportunityStatsWidget->setName('opportunity_statistics');
        $opportunityStatsWidget->setDashboard($dashboard);
        $opportunityStatsWidget->setLayoutPosition($position);
        // collect and split options from old widget
        $leadWidgetOptions = [];
        $opportunityWidgetOptions = [];

        foreach ($options as $key => $option) {
            switch ($key) {
                case 'title':
                    break;
                case 'subWidgets':
                    $leadSubwidgetIds = ['new_leads_count', 'open_leads_count'];
                    $opportunitySubwidgetIds = [
                        'new_opportunities_count',
                        'new_opportunities_amount',
                        'won_opportunities_to_date_count',
                        'won_opportunities_to_date_amount'
                    ];

                    $leadWidgetOptions[$key] = $this->getSubwidgets($option, $leadSubwidgetIds);
                    $opportunityWidgetOptions[$key] = $this->getSubwidgets($option, $opportunitySubwidgetIds);
                    break;
                default:
                    $leadWidgetOptions[$key] = $option;
                    $opportunityWidgetOptions[$key] = $option;
            }
        }

        $leadStatsWidget->setOptions($leadWidgetOptions);
        $opportunityStatsWidget->setOptions($opportunityWidgetOptions);

        $manager->remove($oldWidget);
        $manager->persist($leadStatsWidget);
        $manager->persist($opportunityStatsWidget);
        $manager->flush();
    }

    private function getSubwidgets(array $option, array $subwidgetIds): array
    {
        $items = [];
        if (isset($option['items'])) {
            foreach ($option['items'] as $item) {
                if (isset($item['id']) && in_array($item['id'], $subwidgetIds)) {
                    $items[] = $item;
                }
            }
        }

        return ['items' => $items];
    }
}
