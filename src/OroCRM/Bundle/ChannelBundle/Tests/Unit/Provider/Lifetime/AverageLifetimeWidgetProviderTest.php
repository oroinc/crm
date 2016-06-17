<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Provider\Lifetime;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\DashboardBundle\Filter\DateFilterProcessor;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

use OroCRM\Bundle\ChannelBundle\Provider\Lifetime\AverageLifetimeWidgetProvider;

class AverageLifetimeWidgetProviderTest extends \PHPUnit_Framework_TestCase
{
    const TEST_TZ = 'UTC';

    /** @var ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject */
    protected $registry;

    /** @var AclHelper|\PHPUnit_Framework_MockObject_MockObject */
    protected $aclHelper;

    /** @var LocaleSettings|\PHPUnit_Framework_MockObject_MockObject */
    protected $localeSettings;

    /** @var DateFilterProcessor|\PHPUnit_Framework_MockObject_MockObject */
    protected $dateFilterProcessor;

    /** @var AverageLifetimeWidgetProvider */
    protected $provider;

    protected function setUp()
    {
        $this->registry       = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $this->localeSettings = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Model\LocaleSettings')
            ->disableOriginalConstructor()->getMock();
        $this->aclHelper      = $this->getMockBuilder('Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper')
            ->disableOriginalConstructor()->getMock();
        $this->dateFilterProcessor = $this->getMockBuilder('Oro\Bundle\DashboardBundle\Filter\DateFilterProcessor')
            ->disableOriginalConstructor()->getMock();

        $this->provider = new AverageLifetimeWidgetProvider(
            $this->registry,
            $this->localeSettings,
            $this->aclHelper,
            $this->dateFilterProcessor
        );
    }

    protected function tearDown()
    {
        unset($this->provider, $this->registry, $this->aclHelper, $this->localeSettings);
    }

    /**
     * @dataProvider chartDataProvider
     *
     * @param array $channelsData
     * @param array $averageData
     * @param array $expectedResult
     * @param array $dates
     */
    public function testGetChartData(array $channelsData, array $averageData, array $expectedResult, array $dates)
    {
        $channelRepo = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Entity\Repository\ChannelRepository')
            ->disableOriginalConstructor()->getMock();
        $averageRepo = $this
            ->getMockBuilder('OroCRM\Bundle\ChannelBundle\Entity\Repository\LifetimeValueAverageAggregationRepository')
            ->disableOriginalConstructor()->getMock();

        $channelRepo->expects($this->once())->method('getAvailableChannelNames')
            ->with($this->aclHelper)
            ->will($this->returnValue($channelsData));
        $averageRepo->expects($this->once())->method('findForPeriod')
            ->with($this->isInstanceOf('\DateTime'), $this->isInstanceOf('\DateTime'), array_keys($channelsData))
            ->will($this->returnValue($averageData));

        $this->registry->expects($this->any())->method('getRepository')
            ->will(
                $this->returnValueMap(
                    [
                        ['OroCRMChannelBundle:Channel', null, $channelRepo],
                        ['OroCRMChannelBundle:LifetimeValueAverageAggregation', null, $averageRepo]
                    ]
                )
            );

        $this->dateFilterProcessor
            ->expects($this->once())
            ->method('getModifiedDateData')
            ->with($dates)
            ->willReturn(['value' => $dates]);

        $this->assertEquals($expectedResult, $this->provider->getChartData($dates));
    }

    /**
     * @return array
     */
    public function chartDataProvider()
    {
        $now      = new \DateTime('now', new \DateTimeZone(self::TEST_TZ));
        $nowMonth = $now->format('Y-m');

        $channels = [1 => ['name' => 'First'], 3 => ['name' => 'Second']];

        $averageData = [
            ['channelId' => 1, 'year' => $now->format('Y'), 'month' => $now->format('m'), 'amount' => 222],
            ['channelId' => 3, 'year' => $now->format('Y'), 'month' => $now->format('m'), 'amount' => 333]
        ];

        $end = \DateTime::createFromFormat(\DateTime::ISO8601, $now->format('Y-m-01\T00:00:00+0000'));
        $end->add(new \DateInterval('P1M'));
        $start = clone $end;
        $start->sub(new \DateInterval('P1Y'));
        $dates = [];
        /** @var \DateTime $dt */
        foreach (new \DatePeriod($start, new \DateInterval('P1M'), $end) as $dt) {
            $key         = $dt->format('Y-m');
            $dates[$key] = [
                'month_year' => sprintf('%s-01', $key),
                'amount'     => 0
            ];
        }
        $endDateKey = $end->format('Y-m');
        if (!in_array($endDateKey, array_keys($dates))) {
            $dates[$endDateKey] = [
                'month_year' => sprintf('%s-01', $endDateKey),
                'amount'     => 0
            ];
        }

        $expected                        = [];
        $firstDates                      = $dates;
        $firstDates[$nowMonth]['amount'] = 222;
        $expected['First']               = array_values($firstDates);

        $secondDates                      = $dates;
        $secondDates[$nowMonth]['amount'] = 333;
        $expected['Second']               = array_values($secondDates);

        return [
            'regular case' => [
                '$channelsData'   => $channels,
                '$averageData'    => $averageData,
                '$expectedResult' => $expected,
                '$dates'          => [
                    'start' => $start,
                    'end'   => $end
                ]
            ]
        ];
    }
}
