<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider;

use Oro\Bundle\ChartBundle\Model\ConfigProvider;

use OroCRM\Bundle\MagentoBundle\Provider\TrackingCustomerIdentification as TCI;
use OroCRM\Bundle\MagentoBundle\Provider\WebsiteEventsChartProvider;

class WebsiteEventsChartProviderTest extends WebsiteChartProviderTest
{
    public function testShoultReturnChartViewByEventName()
    {
        $data = [
            ['name' => TCI::EVENT_CART_ITEM_ADDED, 'cnt' => 10, 'date' => '2016-05-03'],
            ['name' => TCI::EVENT_CHECKOUT_STARTED, 'cnt' => 17, 'date' => '2016-05-04'],
        ];

        $expectedData = [
            TCI::EVENT_CART_ITEM_ADDED => [
                ['count' => 10, 'date' => '2016-05-03'],
                ['count' => 0, 'date' => '2016-05-04'],
            ],
            TCI::EVENT_CHECKOUT_STARTED => [
                ['count' => 0, 'date' => '2016-05-03'],
                ['count' => 17, 'date' => '2016-05-04'],
            ],
        ];

        $expectedOptions = [
            'name' => 'stackedbar_chart',
            'settings' => ['chartColors' => '#acd39c,#7fab90'],
        ];

        $provider = $this->getWebsiteChartProvider(
            'getCustomerEventsCountByDate',
            $data,
            $expectedData,
            $expectedOptions
        );

        $provider->getChartView([], false);
    }

    /**
     * @param string $method Repository method
     * @param array $data Repository return data
     * @param array $expectedData
     * @param array $expectedOptions
     *
     * @return WebsiteEventsChartProvider
     */
    protected function getWebsiteChartProvider($method, $data, $expectedData, $expectedOptions)
    {
        $eventProvider = $this->getTrackingVisitEventProviderMock($method, $data);
        $configProvider = $this->getConfigProvider();
        $chartViewBuilder = $this->getChartViewBuilderMock($expectedData, $expectedOptions);
        $container = $this->getContainer($chartViewBuilder);

        return new WebsiteEventsChartProvider($eventProvider, $configProvider, $container);
    }

    /**
     * @return ConfigProvider
     */
    protected function getConfigProvider()
    {
        return new ConfigProvider([
            'stackedbar_chart' => [
                'default_settings' => [
                    'chartColors' => '#acd39c,#7fab90',
                ],
            ],
            'website_chart' => [],
        ]);
    }
}
