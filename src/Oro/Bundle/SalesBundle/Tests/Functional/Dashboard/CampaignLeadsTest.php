<?php
namespace Oro\Bundle\SalesBundle\Tests\Functional\Dashboard;

use Oro\Bundle\DashboardBundle\Entity\Widget;
use Oro\Bundle\DashboardBundle\Tests\Functional\AbstractWidgetTestCase;
use Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractDateFilterType;

/**
 * @dbIsolationPerTest
 */
class CampaignLeadsTest extends AbstractWidgetTestCase
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
            'Oro\Bundle\SalesBundle\Tests\Functional\Fixture\LoadCampaignLeadsWidgetFixture'
        ]);

        $this->widget = $this->getReference('widget_campaigns_leads');
    }
    public function testGetWidgetConfigureDialog()
    {
        $this->getConfigureDialog();
    }

    /**
     * @depends      testGetWidgetConfigureDialog
     * @dataProvider widgetProvider
     */
    public function testDateRangeBetweenFilter($requestData)
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
        $this->assertEquals($response->getStatusCode(), 200, "Failed in getting widget view !");
        $this->assertNotEmpty($crawler->html());

        $data = $this->getChartData($crawler);
        $this->assertEquals('Campaign', $data[0]->label);
        $this->assertEquals($requestData['expectedResultCount'], $data[0]->value);
    }

    protected function getConfigureDialog()
    {
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_dashboard_configure',
                ['id' => $this->widget->getId(), '_widgetContainer' => 'dialog']
            )
        );
        $response = $this->client->getResponse();
        $this->assertEquals($response->getStatusCode(), 200, 'Failed in getting configure widget dialog window !');
    }

    /**
     * @return array
     */
    public function widgetProvider()
    {
        return [
            'Opportunity by status with between date range filter' => [
                [
                    'widgetConfig' => [
                        'campaigns_leads[dateRange][part]'   => 'value',
                        'campaigns_leads[dateRange][type]'   => AbstractDateFilterType::TYPE_BETWEEN,
                        'campaigns_leads[dateRange][value][start]'  => '2016-12-28',
                        'campaigns_leads[dateRange][value][end]'    => '2016-12-29'
                    ],
                    'expectedResultCount' => 2
                ],
            ],
            'Opportunity by status with this month date range filter'  => [
                [
                    'widgetConfig' => [
                        'campaigns_leads[dateRange][part]'   => 'value',
                        'campaigns_leads[dateRange][type]'   => AbstractDateFilterType::TYPE_THIS_MONTH
                    ],
                    'expectedResultCount' => 1
                ],
            ],
            'Opportunity by status with this all time date range filter' => [
                [
                    'widgetConfig' => [
                        'campaigns_leads[dateRange][part]'   => 'value',
                        'campaigns_leads[dateRange][type]'   => AbstractDateFilterType::TYPE_ALL_TIME
                    ],
                    'expectedResultCount' => 4
                ],
            ],
        ];
    }
}
