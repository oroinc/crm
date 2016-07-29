<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider;

use Oro\Bundle\ChartBundle\Model\ConfigProvider;

use OroCRM\Bundle\MagentoBundle\Provider\TrackingCustomerIdentification as TCI;
use OroCRM\Bundle\MagentoBundle\Provider\WebsiteChannelChartProvider;

class WebsiteChannelChartProviderTest extends WebsiteChartProviderTest
{
    public function testShoultReturnChartViewByEventNameAndChannel()
    {
        $data = [
            [
                'name' => TCI::EVENT_CART_ITEM_ADDED,
                'cnt' => 10,
                'date' => '2016-05-03',
                'channel' => 'magento',
            ],
            [
                'name' => TCI::EVENT_CHECKOUT_STARTED,
                'cnt' => 17,
                'date' => '2016-05-04',
                'channel' => 'magento',
            ],
            [
                'name' => TCI::EVENT_CART_ITEM_ADDED,
                'cnt' => 188,
                'date' => '2016-05-03',
                'channel' => 'toys store',
            ],
            [
                'name' => TCI::EVENT_CHECKOUT_STARTED,
                'cnt' => 69,
                'date' => '2016-05-04',
                'channel' => 'toys store',
            ],
            [
                'name' => TCI::EVENT_CHECKOUT_STARTED,
                'cnt' => 4,
                'date' => '2016-05-05',
                'channel' => 'toys store',
            ],
        ];

        $expectedData = [
            sprintf('magento - %s', TCI::EVENT_CART_ITEM_ADDED) => [
                ['count' => 10, 'date' => '2016-05-03'],
                ['count' => 0, 'date' => '2016-05-04'],
                ['count' => 0, 'date' => '2016-05-05'],
            ],
            sprintf('magento - %s', TCI::EVENT_CHECKOUT_STARTED) => [
                ['count' => 0, 'date' => '2016-05-03'],
                ['count' => 17, 'date' => '2016-05-04'],
                ['count' => 0, 'date' => '2016-05-05'],
            ],
            sprintf('toys store - %s', TCI::EVENT_CART_ITEM_ADDED) => [
                ['count' => 188, 'date' => '2016-05-03'],
                ['count' => 0, 'date' => '2016-05-04'],
                ['count' => 0, 'date' => '2016-05-05'],
            ],
            sprintf('toys store - %s', TCI::EVENT_CHECKOUT_STARTED) => [
                ['count' => 0, 'date' => '2016-05-03'],
                ['count' => 69, 'date' => '2016-05-04'],
                ['count' => 4, 'date' => '2016-05-05'],
            ],
        ];

        $expectedOptions = [
            'name' => 'stackedbar_chart',
            'settings' => ['chartColors' => '#acd39c,#cefdbb,#7fab90,#98cdac'],
        ];

        $provider = $this->getWebsiteChartProvider(
            'getCustomerEventsCountByDateAndChannel',
            $data,
            $expectedData,
            $expectedOptions
        );

        $provider->getChartView([], true);
    }

    /**
     * @param string $method Repository method
     * @param array $data Repository return data
     * @param array $expectedData
     * @param array $expectedOptions
     *
     * @return WebsiteChannelChartProvider
     */
    protected function getWebsiteChartProvider($method, $data, $expectedData, $expectedOptions)
    {
        $eventProvider = $this->getTrackingVisitEventProviderMock($method, $data);
        $configProvider = $this->getConfigProvider();
        $chartViewBuilder = $this->getChartViewBuilderMock($expectedData, $expectedOptions);
        $container = $this->getContainer($chartViewBuilder);
        
        return new WebsiteChannelChartProvider($eventProvider, $configProvider, $container);
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
