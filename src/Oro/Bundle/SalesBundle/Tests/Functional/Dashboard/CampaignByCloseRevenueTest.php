<?php
namespace Oro\Bundle\SalesBundle\Tests\Functional\Dashboard;

use Oro\Bundle\DashboardBundle\Entity\Widget;
use Oro\Bundle\DashboardBundle\Tests\Functional\AbstractWidgetTestCase;
use Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractDateFilterType;
use Oro\Bundle\SalesBundle\Tests\Functional\Fixture\LoadCampaignByCloseRevenueWidgetFixture;

/**
 * @dbIsolationPerTest
 */
class CampaignByCloseRevenueTest extends AbstractWidgetTestCase
{
    /** @var  Widget */
    protected $widget;

    protected function setUp(): void
    {
        $this->initClient(
            ['debug' => false],
            $this->generateBasicAuthHeader()
        );
        $this->loadFixtures([
            LoadCampaignByCloseRevenueWidgetFixture::class
        ]);

        $this->widget = $this->getReference('widget_campaigns_by_close_revenue');
    }

    /**
     * @dataProvider widgetProvider
     */
    public function testDateRangeAllTypeFilter($requestData)
    {
        $this->configureWidget($this->widget, $requestData['widgetConfig']);

        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_campaign_dashboard_campaigns_by_close_revenue_chart',
                [
                    'widget' => 'campaigns_by_close_revenue',
                    '_widgetId' => $this->widget->getId()
                ]
            )
        );
        $response = $this->client->getResponse();

        $this->assertEquals($response->getStatusCode(), 200, "Failed in getting widget view !");
        $this->assertNotEmpty($crawler->html());

        $chartData = $this->getChartData($crawler);

        //If we have data for chart we need only first campaign
        if ($chartData) {
            $chartData = reset($chartData);
        }

        $this->assertEquals(
            $requestData['expectedResult'],
            round($chartData->value),
            'Revenue for campaign widget calculated incorrectly'
        );
    }

    /**
     * @dataProvider widgetConfigureProvider
     * @array $requestData
     */
    public function testFilterCampaignByNullCloseRevenue(array $requestData)
    {
        $this->configureWidget($this->widget, $requestData['widgetConfig']);
        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_campaign_dashboard_campaigns_by_close_revenue_chart',
                [
                    'widget' => 'campaigns_by_close_revenue',
                    '_widgetId' => $this->widget->getId()
                ]
            )
        );
        $response = $this->client->getResponse();
        $this->assertEquals($response->getStatusCode(), 200, "Failed in getting widget view !");
        $this->assertNotEmpty($crawler->html());
        $chartData = $this->getChartData($crawler);
        $this->assertCount(
            $requestData['expectedCampaignCount'],
            $chartData,
            "Opportunity with null or 0 close revenue is presented"
        );
    }

    /**
     * @return array
     */
    public function widgetConfigureProvider()
    {
        return [
            'Closed lost opportunities' => [
                [
                    'widgetConfig' => [
                        'campaigns_by_close_revenue[dateRange][part]'   => 'value',
                        'campaigns_by_close_revenue[dateRange][type]'   => AbstractDateFilterType::TYPE_ALL_TIME,
                    ],
                    'expectedResult'        => 200, // 2 opportunities * $100
                    'expectedCampaignCount' => 1 // Opportunity with test campaign have null close revenue
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function widgetProvider()
    {
        return [
            'Closed lost opportunities' => [
                [
                    'widgetConfig' => [
                        'campaigns_by_close_revenue[dateRange][part]'   => 'value',
                        'campaigns_by_close_revenue[dateRange][type]'   => AbstractDateFilterType::TYPE_ALL_TIME,
                    ],
                    'expectedResult' => 200 // 2 opportunities * $100
                ],
            ],
            'Opportunities for today' => [
                [
                    'widgetConfig' => [
                        'campaigns_by_close_revenue[dateRange][part]'   => 'value',
                        'campaigns_by_close_revenue[dateRange][type]'   => AbstractDateFilterType::TYPE_BETWEEN,
                        'campaigns_by_close_revenue[dateRange][value][start]'   => '2016-12-29 00:00:00',
                        'campaigns_by_close_revenue[dateRange][value][end]'   => '2016-12-29 23:59:59',
                    ],
                    'expectedResult' => 100 // 1 opportunity * $100
                ],
            ]
        ];
    }
}
