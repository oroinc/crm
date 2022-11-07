<?php
namespace Oro\Bundle\SalesBundle\Tests\Functional\Dashboard;

use Oro\Bundle\DashboardBundle\Entity\Widget;
use Oro\Bundle\DashboardBundle\Tests\Functional\AbstractWidgetTestCase;
use Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractDateFilterType;
use Oro\Bundle\SalesBundle\Tests\Functional\Fixture\LoadCampaignLeadsWidgetFixture;

/**
 * @dbIsolationPerTest
 */
class CampaignLeadsTest extends AbstractWidgetTestCase
{
    private Widget $widget;

    protected function setUp(): void
    {
        $this->initClient(['debug' => false], $this->generateBasicAuthHeader());
        $this->loadFixtures([LoadCampaignLeadsWidgetFixture::class]);
        $this->widget = $this->getReference('widget_campaigns_leads');
    }

    public function testGetWidgetConfigureDialog()
    {
        $this->getConfigureDialog();
    }

    /**
     * @depends testGetWidgetConfigureDialog
     * @dataProvider widgetProvider
     */
    public function testDateRangeBetweenFilter(array $requestData)
    {
        $this->configureWidget($this->widget, $requestData['widgetConfig']);

        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_campaign_dashboard_campaigns_leads_chart',
                [
                    'widget' => 'campaigns_leads',
                    '_widgetId' => $this->widget->getId()
                ]
            )
        );
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), 'Failed in getting widget view');
        $this->assertNotEmpty($crawler->html());

        $data = $this->getChartData($crawler);
        $this->assertCount($requestData['itemCount'], $data);
        foreach ($data as $item) {
            if ($item->label === 'Campaign') {
                $this->assertEquals($requestData['expectedLeadsCount'], $item->value);
            }
        }
    }

    private function getConfigureDialog()
    {
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_dashboard_configure',
                ['id' => $this->widget->getId(), '_widgetContainer' => 'dialog']
            )
        );
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), 'Failed in getting configure widget dialog window !');
    }

    public function widgetProvider(): array
    {
        return [
            'Campaigns with lead between date range filter' => [
                [
                    'widgetConfig' => [
                        'campaigns_leads[dateRange][part]'         => 'value',
                        'campaigns_leads[dateRange][type]'         => AbstractDateFilterType::TYPE_BETWEEN,
                        'campaigns_leads[dateRange][value][start]' => '2016-12-28',
                        'campaigns_leads[dateRange][value][end]'   => '2016-12-29',
                        'campaigns_leads[hideCampaign]'            => '1',
                        'campaigns_leads[maxResults]'              => 5,
                    ],
                    'itemCount' => 1,
                    'expectedLeadsCount' => 2
                ],
            ],
            'Campaigns with leads within this month date range filter'  => [
                [
                    'widgetConfig' => [
                        'campaigns_leads[dateRange][part]' => 'value',
                        'campaigns_leads[dateRange][type]' => AbstractDateFilterType::TYPE_THIS_MONTH,
                        'campaigns_leads[hideCampaign]'    => '1',
                        'campaigns_leads[maxResults]'      => 5,
                    ],
                    'itemCount' => 1,
                    'expectedLeadsCount' => 1
                ],
            ],
            'Campaigns with leads in all time date range filter' => [
                [
                    'widgetConfig' => [
                        'campaigns_leads[dateRange][part]' => 'value',
                        'campaigns_leads[dateRange][type]' => AbstractDateFilterType::TYPE_ALL_TIME,
                        'campaigns_leads[hideCampaign]'    => '1',
                        'campaigns_leads[maxResults]'      => 5,
                    ],
                    'itemCount' => 1,
                    'expectedLeadsCount' => 4
                ],
            ],
            'All campaigns in all time date range filter' => [
                [
                    'widgetConfig' => [
                        'campaigns_leads[dateRange][part]' => 'value',
                        'campaigns_leads[dateRange][type]' => AbstractDateFilterType::TYPE_ALL_TIME,
                        'campaigns_leads[hideCampaign]'    => '0',
                        'campaigns_leads[maxResults]'      => 5,
                    ],
                    'itemCount' => 2,
                    'expectedLeadsCount' => 4
                ],
            ],
        ];
    }
}
