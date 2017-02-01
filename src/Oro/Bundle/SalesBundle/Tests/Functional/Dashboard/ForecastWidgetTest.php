<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Dashboard;

use Oro\Bundle\DashboardBundle\Tests\Functional\AbstractWidgetTestCase;
use Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractDateFilterType;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class ForecastWidgetTest extends AbstractWidgetTestCase
{
    protected function setUp()
    {
        $this->initClient(
            ['debug' => false],
            array_merge($this->generateBasicAuthHeader(), array('HTTP_X-CSRF-Header' => 1))
        );
        $this->loadFixtures([
            'Oro\Bundle\SalesBundle\Tests\Functional\Fixture\LoadForecastWidgetFixtures'
        ]);
    }

    /**
     * @dataProvider getTimeFilter
     */
    public function testCloseDateFilterSuccess($dateRangeType, $inProgressCount)
    {
        $this->markTestSkipped('Skipped until CRM-7567 gets resolved');
        $widget = $this->getReference('widget_forecast');

        $this->configureWidget($widget, [
            'forecast_of_opportunities[dateRange][part]' => 'value',
            'forecast_of_opportunities[dateRange][type]' => $dateRangeType,
            'forecast_of_opportunities[subWidgets][items][0][id]' => 'in_progress',
            'forecast_of_opportunities[subWidgets][items][0][order]' => 0,
            'forecast_of_opportunities[subWidgets][items][0][show]' => 'on'
        ]);

        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_dashboard_itemized_data_widget',
                [
                    'widget' => 'forecast_of_opportunities',
                    'bundle' => 'OroDashboardBundle',
                    'name' => 'bigNumbers',
                    '_widgetId' => $widget->getId()
                ]
            )
        );
        $response = $this->client->getResponse();
        $this->assertEquals($response->getStatusCode(), 200, "Failed in gettting widget view !");

        $subWidgetValue = $crawler->filter('.sub-widget .value')->text();

        $this->assertEquals(
            $inProgressCount,
            $subWidgetValue,
            '"In progress" metric is not correct. Check calculation in ForecastProvider!'
        );
    }

    public function getTimeFilter()
    {
        return [
            'Close Date: All time' => [
                'date_range_type' => AbstractDateFilterType::TYPE_ALL_TIME,
                'in_progress_count' => 3,
            ],
            'Close Date: This month' => [
                'date_range_type' => AbstractDateFilterType::TYPE_THIS_MONTH,
                'in_progress_count' => 2,
            ],
        ];
    }
}
