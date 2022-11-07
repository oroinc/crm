<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Dashboard;

use Oro\Bundle\DashboardBundle\Entity\Widget;
use Oro\Bundle\DashboardBundle\Tests\Functional\AbstractWidgetTestCase;
use Oro\Bundle\EntityBundle\Tests\Functional\DataFixtures\LoadBusinessUnitData;
use Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractDateFilterType;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\SalesBundle\Tests\Functional\Fixture\LoadOpportunityByStatusWidgetFixture;

/**
 * @dbIsolationPerTest
 */
class OpportunityByStatusTest extends AbstractWidgetTestCase
{
    private Widget $widget;

    protected function setUp(): void
    {
        $this->initClient(['debug' => false], $this->generateBasicAuthHeader());
        $this->loadFixtures([
            LoadOpportunityByStatusWidgetFixture::class,
            LoadBusinessUnitData::class
        ]);
        $this->widget = $this->getReference('widget_opportunity_by_status');
    }

    public function testGetWidgetConfigureDialog()
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
                'oro_sales_dashboard_opportunity_by_state_chart',
                [
                    'widget'    => 'opportunities_by_state',
                    '_widgetId' => $this->widget->getId()
                ]
            )
        );
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), 'Failed in getting widget view');
        $this->assertNotEmpty($crawler->html());

        $data = $this->getChartData($crawler);
        $this->assertEquals('Open', $data[0]->label);
        $this->assertEquals($requestData['expectedResultCount'], $data[0]->value);
    }

    public function testBusinessUnitFiltersRendering()
    {
        /** @var BusinessUnit $businessUnit */
        $businessUnit = $this->getReference('TestBusinessUnit');
        $this->configureWidget($this->widget, [
            'opportunities_by_state[owners][businessUnits]' => $businessUnit->getId()
        ]);

        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_sales_dashboard_opportunity_by_state_chart',
                [
                    'widget'    => 'opportunities_by_state',
                    '_widgetId' => $this->widget->getId()
                ]
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), 'Failed in getting widget view');
        $this->assertNotEmpty($crawler->html());

        $this->assertEquals(
            'Business Unit: TestBusinessUnit',
            $crawler->filter('.widget-config-data')->text(),
            'Widget filters section is incorrect, please check BusinessUnitEntityNameProvider'
        );
    }

    public function widgetProvider(): array
    {
        return [
            'Opportunity by status with between date range filter'       => [
                [
                    'widgetConfig'        => [
                        'opportunities_by_state[dateRange][part]'         => 'value',
                        'opportunities_by_state[dateRange][type]'         => AbstractDateFilterType::TYPE_BETWEEN,
                        'opportunities_by_state[dateRange][value][start]' => '2016-12-28',
                        'opportunities_by_state[dateRange][value][end]'   => '2016-12-29',
                        'opportunities_by_state[useQuantityAsData]'       => 1
                    ],
                    'expectedResultCount' => 2
                ],
            ],
            'Opportunity by status with this month date range filter'    => [
                [
                    'widgetConfig'        => [
                        'opportunities_by_state[dateRange][part]'   => 'value',
                        'opportunities_by_state[dateRange][type]'   => AbstractDateFilterType::TYPE_THIS_MONTH,
                        'opportunities_by_state[useQuantityAsData]' => 1
                    ],
                    'expectedResultCount' => 1
                ],
            ],
            'Opportunity by status with this all time date range filter' => [
                [
                    'widgetConfig'        => [
                        'opportunities_by_state[dateRange][part]'   => 'value',
                        'opportunities_by_state[dateRange][type]'   => AbstractDateFilterType::TYPE_ALL_TIME,
                        'opportunities_by_state[useQuantityAsData]' => 1
                    ],
                    'expectedResultCount' => 4
                ],
            ],
        ];
    }
}
