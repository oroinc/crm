<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Dashboard;

use Oro\Bundle\ChartBundle\Model\ChartViewBuilder;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractDateFilterType;
use Oro\Bundle\MagentoBundle\Dashboard\OrderDataProvider as DataProvider;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadOrderDataWithFixedDate;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class OrderDataProviderTest extends WebTestCase
{
    /** @var DataProvider */
    protected $orderDataProvider;

    /** @var ConfigManager */
    protected $configManager;

    /** @var string */
    protected $savedTimezone;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->markTestSkipped('Should be fixed in CRM-8594');
        parent::setUp();
        $this->initClient();

        $this->configManager = $this
            ->getClientInstance()
            ->getContainer()
            ->get('oro_config.user');

        $this->savedTimezone = $this->configManager->get('oro_locale.timezone');
        $this->configManager->set('oro_locale.timezone', 'Europe/Berlin');

        $this->orderDataProvider = $this
            ->getClientInstance()
            ->getContainer()
            ->get('oro_magento.dashboard.data_provider.order');

        $this->loadFixtures(
            [
                LoadOrderDataWithFixedDate::class
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->configManager->set('oro_locale.timezone', $this->savedTimezone);
        unset(
            $this->orderDataProvider,
            $this->configManager
        );
        parent::tearDown();
    }

    public function testGetRevenueOverTimeChartViewWithNonUTCTimezoneOnSelectedDate()
    {
        /**
         * @var $chartViewBuilder ChartViewBuilder | \PHPUnit\Framework\MockObject\MockObject
         */
        $chartViewBuilder = $this
            ->getMockBuilder(ChartViewBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $chartViewBuilder
            ->expects($this->atLeastOnce())
            ->method('setOptions')
            ->willReturnSelf();

        $chartViewBuilder
            ->expects($this->once())
            ->method('getView')
            ->willReturn(true);

        $chartViewBuilder
            ->expects($this->once())
            ->method('setArrayData')
            ->with(
                $this->equalTo($this->getDataForTestGetRevenueOver())
            )
            ->willReturnSelf();

        $timezoneFromConfig = new \DateTimeZone($this->configManager->get('oro_locale.timezone'));

        $this->orderDataProvider->getRevenueOverTimeChartView(
            $chartViewBuilder,
            [
                'start' => new \DateTime('2017-07-01 00:00:00', $timezoneFromConfig),
                'end'   => new \DateTime('2017-07-04 23:59:59', $timezoneFromConfig),
                'type'  => AbstractDateFilterType::TYPE_BETWEEN,
                'part'  => 'value'
            ]
        );
    }

    /**
     * @return array
     */
    protected function getDataForTestGetRevenueOver()
    {
        return [
            'Jun 27, 2017 - Jul 1, 2017' => [
                [
                    'date' => '2017-07-01',
                ],
                [
                    'date' => '2017-07-02',
                ],
                [
                    'date' => '2017-07-03',
                ],
                [
                    'date' => '2017-07-04',
                    'amount'  => '13.4500'
                ]
            ],
            'Jul 1, 2017 - Jul 4, 2017'  => [
                [
                    'date' => '2017-07-01',
                    'amount'  => '11.1000'
                ],
                [
                    'date' => '2017-07-02',
                ],
                [
                    'date' => '2017-07-03',
                ],
                [
                    'date' => '2017-07-04',
                    'amount'  => '11.1000'
                ]
            ],
        ];
    }
}
