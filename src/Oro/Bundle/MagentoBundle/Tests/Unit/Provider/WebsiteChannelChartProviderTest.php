<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider;

use Oro\Bundle\ChartBundle\Model\ConfigProvider;
use Oro\Bundle\MagentoBundle\Provider\TrackingCustomerIdentificationEvents as TCI;
use Oro\Bundle\MagentoBundle\Provider\WebsiteChannelChartProvider;

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
            sprintf('magento - %s', WebsiteChannelChartProvider::$legendLabelsMap[TCI::EVENT_CART_ITEM_ADDED]) => [
                ['count' => 10, 'date' => '2016-05-03'],
                ['count' => 0, 'date' => '2016-05-04'],
                ['count' => 0, 'date' => '2016-05-05'],
            ],
            sprintf('magento - %s', WebsiteChannelChartProvider::$legendLabelsMap[TCI::EVENT_CHECKOUT_STARTED]) => [
                ['count' => 0, 'date' => '2016-05-03'],
                ['count' => 17, 'date' => '2016-05-04'],
                ['count' => 0, 'date' => '2016-05-05'],
            ],
            sprintf('toys store - %s', WebsiteChannelChartProvider::$legendLabelsMap[TCI::EVENT_CART_ITEM_ADDED]) => [
                ['count' => 188, 'date' => '2016-05-03'],
                ['count' => 0, 'date' => '2016-05-04'],
                ['count' => 0, 'date' => '2016-05-05'],
            ],
            sprintf('toys store - %s', WebsiteChannelChartProvider::$legendLabelsMap[TCI::EVENT_CHECKOUT_STARTED]) => [
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
        $translator = $this->getTranslator();

        return new WebsiteChannelChartProvider($eventProvider, $configProvider, $chartViewBuilder, $translator);
    }

    /**
     * @return ConfigProvider
     */
    protected function getConfigProvider()
    {
        $chartConfigs = [
            'stackedbar_chart' => [
                'default_settings' => [
                    'chartColors' => '#acd39c,#7fab90'
                ]
            ],
            'website_chart' => []
        ];

        $configProvider = $this->createMock(ConfigProvider::class);
        $configProvider->expects($this->any())
            ->method('getChartConfig')
            ->willReturnCallback(function ($name) use ($chartConfigs) {
                return $chartConfigs[$name];
            });

        return $configProvider;
    }
}
