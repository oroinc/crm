<?php
namespace Oro\Bundle\SalesBundle\Tests\Functional\Dashboard;

use Oro\Bundle\DashboardBundle\Entity\Widget;
use Oro\Bundle\DashboardBundle\Tests\Functional\AbstractWidgetTestCase;
use Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractDateFilterType;
use Oro\Bundle\SalesBundle\Tests\Functional\Fixture\LoadCampaignOpportunityWidgetFixture;

/**
 * @dbIsolationPerTest
 */
class CampaignOpportunityTest extends AbstractWidgetTestCase
{
    private Widget $widget;

    protected function setUp(): void
    {
        $this->initClient(['debug' => false], $this->generateBasicAuthHeader());
        $this->loadFixtures([LoadCampaignOpportunityWidgetFixture::class]);
        $this->widget = $this->getReference('widget_campaigns_opportunity');
    }

    public function testGetWidgetConfigureDialog(): void
    {
        $this->getConfigureDialog();
    }

    /**
     * @depends testGetWidgetConfigureDialog
     * @dataProvider widgetProvider
     */
    public function testDateRangeBetweenFilter(array $requestData): void
    {
        $this->configureWidget($this->widget, $requestData['widgetConfig']);

        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_campaign_dashboard_campaigns_opportunity_chart',
                [
                    'widget' => 'campaigns_opportunity',
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
            if ($item->label === 'Default campaign') {
                $this->assertEquals($requestData['expectedResultCount'], $item->value);
            }
        }
    }

    private function getConfigureDialog(): void
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
            'Campaigns with opportunities between date range filter' => [
                [
                    'widgetConfig' => [
                        'campaigns_opportunity[dateRange][part]'         => 'value',
                        'campaigns_opportunity[dateRange][type]'         => AbstractDateFilterType::TYPE_BETWEEN,
                        'campaigns_opportunity[dateRange][value][start]' => '2016-12-28',
                        'campaigns_opportunity[dateRange][value][end]'   => '2016-12-29',
                        'campaigns_opportunity[maxResults]'              => 5,
                    ],
                    'itemCount' => 2,
                    'expectedResultCount' => 4
                ],
            ],
            'Campaigns with opportunities in all time date range filter' => [
                [
                    'widgetConfig' => [
                        'campaigns_opportunity[dateRange][part]' => 'value',
                        'campaigns_opportunity[dateRange][type]' => AbstractDateFilterType::TYPE_ALL_TIME,
                        'campaigns_opportunity[maxResults]'      => 5,
                    ],
                    'itemCount' => 2,
                    'expectedResultCount' => 4
                ],
            ],
        ];
    }
}
