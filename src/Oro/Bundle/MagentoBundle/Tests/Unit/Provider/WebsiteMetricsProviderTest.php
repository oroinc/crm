<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider;

use Oro\Bundle\MagentoBundle\Provider\TrackingCustomerIdentificationEvents as TCI;
use Oro\Bundle\MagentoBundle\Provider\TrackingVisitEventProvider;
use Oro\Bundle\MagentoBundle\Provider\TrackingVisitProvider;
use Oro\Bundle\MagentoBundle\Provider\WebsiteMetricsProvider;

class WebsiteMetricsProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldCorrectlyMapData()
    {
        $provider = $this->getWebsiteMetricsProvider(
            [
                'count' => 10,
                'last' => '2016-06-10',
                'monthly' => 2,
            ],
            [
                TCI::EVENT_CART_ITEM_ADDED => [
                    'count' => 1337,
                    'last' => '2016-12-10',
                ],
                TCI::EVENT_CHECKOUT_STARTED => [
                    'count' => 2,
                    'last' => '2015-05-03',
                ],
                TCI::EVENT_CUSTOMER_LOGIN => [
                    'count' => 2000,
                    'last' => '2016-05-03',
                ],
                TCI::EVENT_VISIT => [
                    'count' => 180,
                    'last' => '2016-05-03',
                ],
            ],
            'http://test.com/',
            'http://stackoverflow.com/'
        );

        $expectedData = [
            'metrics' => [
                'page_view_count'         => 180,
                'checkout_count'          => 2,
                'item_added_count'        => 1337,
                'visit_count'             => 10,
                'average_visit_views'     => 18,
                'average_visit_items'     => 133.7,
                'average_visit_checkouts' => 5,
                'average_visit_monthly'   => 2,
                'most_viewed_page'        => 'http://stackoverflow.com/',
                'last_viewed_page'        => 'http://test.com/',
                'last_login'              => '2016-05-03',
                'last_item'               => '2016-12-10',
                'last_checkout'           => '2015-05-03',
                'last_visit'              => '2016-06-10',
            ],
        ];

        $this->assertSame($expectedData, $provider->getTemplateData([]));
    }

    public function testShouldReturnDefaultValuesWhenMissingData()
    {
        $provider = $this->getWebsiteMetricsProvider(
            [
                'count' => 0,
                'last' => null,
                'monthly' => 0,
            ],
            [],
            null,
            null
        );

        $expectedData = [
            'metrics' => [
                'page_view_count'         => 0,
                'checkout_count'          => 0,
                'item_added_count'        => 0,
                'visit_count'             => 0,
                'average_visit_views'     => 0,
                'average_visit_items'     => 0,
                'average_visit_checkouts' => 0,
                'average_visit_monthly'   => 0,
                'most_viewed_page'        => null,
                'last_viewed_page'        => null,
                'last_login'              => null,
                'last_item'               => null,
                'last_checkout'           => null,
                'last_visit'              => null,
            ],
        ];

        $this->assertSame($expectedData, $provider->getTemplateData([]));
    }

    /**
     * @param array $visitAggregates
     * @param array $visitEventAggregates
     * @param string $lastViewedPage
     * @param string $mostViewedPage
     *
     * @return WebsiteMetricsProvider
     */
    protected function getWebsiteMetricsProvider(
        array $visitAggregates,
        array $visitEventAggregates,
        $lastViewedPage,
        $mostViewedPage
    ) {
        $visitProvider = $this->getTrackingVisitProviderMock($visitAggregates);
        $visitEventProvider = $this->getTrackingVisitEventProviderMock(
            $visitEventAggregates,
            $lastViewedPage,
            $mostViewedPage
        );

        return new WebsiteMetricsProvider($visitProvider, $visitEventProvider);
    }

    /**
     * @param array $data
     *
     * @return TrackingVisitProvider
     */
    protected function getTrackingVisitProviderMock(array $data)
    {
        $visitProvider = $this->getMockBuilder(TrackingVisitProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $visitProvider->expects($this->any())
            ->method('getAggregates')
            ->willReturn($data);

        return $visitProvider;
    }

    /**
     * @param array $aggregates
     * @param string $lastViewedPage
     * @param string $mostViewedPage
     *
     * @return TrackingVisitEventProvider
     */
    protected function getTrackingVisitEventProviderMock(
        array $aggregates,
        $lastViewedPage,
        $mostViewedPage
    ) {
        $visitEventProvider = $this->getMockBuilder(TrackingVisitEventProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $visitEventProvider->expects($this->any())
            ->method('getCustomerEventAggregates')
            ->willReturn($aggregates);

        $visitEventProvider->expects($this->any())
            ->method('getLastViewedPage')
            ->willReturn($lastViewedPage);

        $visitEventProvider->expects($this->any())
            ->method('getMostViewedPage')
            ->willReturn($mostViewedPage);

        return $visitEventProvider;
    }
}
