<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Provider\Lifetime;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Entity\LifetimeValueAverageAggregation;
use Oro\Bundle\ChannelBundle\Entity\Repository\ChannelRepository;
use Oro\Bundle\ChannelBundle\Entity\Repository\LifetimeValueAverageAggregationRepository;
use Oro\Bundle\ChannelBundle\Provider\Lifetime\AverageLifetimeWidgetProvider;
use Oro\Bundle\DashboardBundle\Filter\DateFilterProcessor;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AverageLifetimeWidgetProviderTest extends TestCase
{
    private const TEST_TZ = 'UTC';

    private ManagerRegistry&MockObject $registry;
    private AclHelper&MockObject $aclHelper;
    private LocaleSettings&MockObject $localeSettings;
    private DateFilterProcessor&MockObject $dateFilterProcessor;
    private AverageLifetimeWidgetProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->localeSettings = $this->createMock(LocaleSettings::class);
        $this->aclHelper = $this->createMock(AclHelper::class);
        $this->dateFilterProcessor = $this->createMock(DateFilterProcessor::class);

        $this->provider = new AverageLifetimeWidgetProvider(
            $this->registry,
            $this->localeSettings,
            $this->aclHelper,
            $this->dateFilterProcessor
        );
    }

    /**
     * @dataProvider chartDataProvider
     */
    public function testGetChartData(array $channelsData, array $averageData, array $expectedResult, array $dates): void
    {
        $channelRepo = $this->createMock(ChannelRepository::class);
        $averageRepo = $this->createMock(LifetimeValueAverageAggregationRepository::class);

        $channelRepo->expects($this->once())
            ->method('getAvailableChannelNames')
            ->with($this->aclHelper)
            ->willReturn($channelsData);
        $averageRepo->expects($this->once())
            ->method('findForPeriod')
            ->with(
                $this->isInstanceOf(\DateTime::class),
                $this->isInstanceOf(\DateTime::class),
                array_keys($channelsData)
            )
            ->willReturn($averageData);

        $this->registry->expects($this->any())
            ->method('getRepository')
            ->willReturnMap([
                [Channel::class, null, $channelRepo],
                [LifetimeValueAverageAggregation::class, null, $averageRepo]
            ]);

        $this->dateFilterProcessor->expects($this->once())
            ->method('getModifiedDateData')
            ->with($dates)
            ->willReturn(['value' => $dates]);

        $this->assertEquals($expectedResult, $this->provider->getChartData($dates));
    }

    public function chartDataProvider(): array
    {
        $now = new \DateTime('now', new \DateTimeZone(self::TEST_TZ));
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
