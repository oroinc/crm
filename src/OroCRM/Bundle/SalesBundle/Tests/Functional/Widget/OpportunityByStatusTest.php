<?php
namespace OroCRM\Bundle\SalesBundle\Tests\Functional\Widget;

use Oro\Bundle\DashboardBundle\Entity\Widget;
use Oro\Bundle\DashboardBundle\Tests\Functional\AbstractWidgetTestCase;
use Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractDateFilterType;

/**
 * @dbIsolationPerTest
 */
class OpportunityByStatusTest extends AbstractWidgetTestCase
{
    /** @var  Widget */
    protected $widget;

    public function setUp()
    {
        $this->initClient(
            ['debug' => false],
            array_merge($this->generateBasicAuthHeader(), array('HTTP_X-CSRF-Header' => 1))
        );
        $this->loadFixtures([
            'OroCRM\Bundle\SalesBundle\Tests\Functional\Fixture\LoadOpportunityByStatusWidgetFixture'
        ]);
        $this->widget = $this->getReference('widget_opportunity_by_status');
    }

    public function testGetWidgetConfigureDialog()
    {
        $this->getConfigureDialog();
    }

    /**
     * @depends      testGetWidgetConfigureDialog
     * @dataProvider widgetProvider
     * @param $requestData
     */
    public function testDateRangeBetweenFilter($requestData)
    {
        $this->configureWidget($this->widget, $requestData['widgetConfig']);
        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'orocrm_sales_dashboard_opportunity_by_state_chart',
                [
                    'widget' => 'opportunities_by_state',
                    '_widgetId' => $this->widget->getId()
                ]
            )
        );
        $response = $this->client->getResponse();
        $this->assertEquals($response->getStatusCode(), 200, "Failed in getting widget view !");
        $this->assertNotEmpty($crawler->html());
        $data = $this->getChartData($crawler);
        $this->assertEquals('Open', $data[0]->label);
        $this->assertEquals($requestData['expectedResultCount'], $data[0]->value);
    }

    /**
     * @param $crawler
     * @return array
     */
    protected function getChartData($crawler)
    {
        $dataComponent = $crawler->filter('.column-chart');
        if ($dataComponent->extract(['data-page-component-options'])) {
            $data = $dataComponent->extract(['data-page-component-options']);
            $data = json_decode($data[0]);
            return $data->chartOptions->dataSource->data;
        } else {
            $dataComponent = $crawler->filter('.opportunities-by-state-widget-content>div');
            $data = $dataComponent->extract(['data-page-component-options']);
            $data = json_decode($data[0]);
            return $data->data;
        }
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
                        'opportunities_by_state[dateRange][part]'   => 'value',
                        'opportunities_by_state[dateRange][type]'   => AbstractDateFilterType::TYPE_BETWEEN,
                        'opportunities_by_state[dateRange][value][start]'  => '2016-12-28',
                        'opportunities_by_state[dateRange][value][end]'    => '2016-12-29',
                        'opportunities_by_state[useQuantityAsData]' => 1
                    ],
                    'expectedResultCount' => 2
                ],
            ],
            'Opportunity by status with this month date range filter'  => [
                [
                    'widgetConfig' => [
                        'opportunities_by_state[dateRange][part]'   => 'value',
                        'opportunities_by_state[dateRange][type]'   => AbstractDateFilterType::TYPE_THIS_MONTH,
                        'opportunities_by_state[useQuantityAsData]' => 1
                    ],
                    'expectedResultCount' => 1
                ],
            ],
            'Opportunity by status with this all time date range filter' => [
                [
                    'widgetConfig' => [
                        'opportunities_by_state[dateRange][part]'   => 'value',
                        'opportunities_by_state[dateRange][type]'   => AbstractDateFilterType::TYPE_ALL_TIME,
                        'opportunities_by_state[useQuantityAsData]' => 1
                    ],
                    'expectedResultCount' => 5
                ],
            ],
        ];
    }
}
