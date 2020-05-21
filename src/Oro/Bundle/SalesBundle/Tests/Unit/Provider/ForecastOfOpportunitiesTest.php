<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Provider;

use Oro\Bundle\DashboardBundle\Helper\DateHelper;
use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\DashboardBundle\Provider\Converters\FilterDateRangeConverter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractDateFilterType;
use Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatterInterface;
use Oro\Bundle\LocaleBundle\Formatter\NumberFormatter;
use Oro\Bundle\SalesBundle\Provider\ForecastOfOpportunities;
use Oro\Bundle\SalesBundle\Provider\Opportunity\ForecastProvider;
use Symfony\Contracts\Translation\TranslatorInterface;

class ForecastOfOpportunitiesTest extends \PHPUnit\Framework\TestCase
{
    /** @var ForecastOfOpportunities */
    protected $provider;

    /** @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $translator;

    /** @var NumberFormatter|\PHPUnit\Framework\MockObject\MockObject */
    protected $numberFormatter;

    /** @var DateTimeFormatterInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $dateTimeFormatter;

    /** @var ForecastProvider|\PHPUnit\Framework\MockObject\MockObject */
    protected $forecastProvider;

    /** @var DateHelper|\PHPUnit\Framework\MockObject\MockObject */
    protected $dateHelper;

    /** @var FilterDateRangeConverter|\PHPUnit\Framework\MockObject\MockObject */
    protected $filterDateRangeConverter;

    protected function setUp(): void
    {
        $this->translator = $this->getMockBuilder('Oro\Bundle\TranslationBundle\Translation\Translator')
            ->disableOriginalConstructor()
            ->getMock();

        $this->numberFormatter = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Formatter\NumberFormatter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->numberFormatter
            ->expects($this->any())
            ->method($this->anything())
            ->withAnyParameters()
            ->will($this->returnArgument(0));

        $this->dateTimeFormatter = $this
            ->getMockBuilder('Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatterInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->dateTimeFormatter
            ->expects($this->any())
            ->method($this->anything())
            ->withAnyParameters()
            ->will($this->returnArgument(0));

        $this->dateHelper = $this->getMockBuilder('Oro\Bundle\DashboardBundle\Helper\DateHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->forecastProvider = $this
            ->getMockBuilder('Oro\Bundle\SalesBundle\Provider\Opportunity\ForecastProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterDateRangeConverter = $this
            ->getMockBuilder('Oro\Bundle\DashboardBundle\Provider\Converters\FilterDateRangeConverter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider = new ForecastOfOpportunities(
            $this->numberFormatter,
            $this->dateTimeFormatter,
            $this->translator,
            $this->dateHelper,
            $this->forecastProvider,
            $this->filterDateRangeConverter
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->numberFormatter,
            $this->dateTimeFormatter,
            $this->translator,
            $this->dateHelper,
            $this->widgetProviderFilter,
            $this->provider
        );
    }

    public function testForecastOfOpportunitiesValues()
    {
        $options       = [
            'dateRange' => ['start' => null, 'end' => null]
        ];
        $widgetOptions = new WidgetOptionBag($options);

        $this->forecastProvider->expects($this->exactly(3))
            ->method('getForecastData')
            ->with($widgetOptions, null, null)
            ->will($this->returnValue(['inProgressCount' => 5, 'budgetAmount' => 1000, 'weightedForecast' => 500]));

        $result = $this->provider
            ->getForecastOfOpportunitiesValues($widgetOptions, 'inProgressCount', 'integer', false);
        $this->assertEquals(['value' => 5], $result);

        $result = $this->provider
            ->getForecastOfOpportunitiesValues($widgetOptions, 'budgetAmount', 'currency', false);
        $this->assertEquals(['value' => 1000], $result);

        $result = $this->provider
            ->getForecastOfOpportunitiesValues($widgetOptions, 'weightedForecast', 'currency', false);
        $this->assertEquals(['value' => 500], $result);
    }

    public function testForecastOfOpportunitiesValuesWithCompareDate()
    {
        $start = new \DateTime();
        $start->setDate(2016, 6, 1)->setTime(0, 0, 0);
        $end = clone $start;
        $end->setDate(2016, 7, 1);
        $diff      = $start->diff($end);
        $prevStart = clone $start;
        $prevStart->sub($diff);
        $prevEnd = clone $end;
        $prevEnd->sub($diff);
        $prevStart->setTime(0, 0, 0);
        $prevEnd->setTime(23, 59, 59);

        $dateRange     = [
            'start' => $start,
            'end' => $end,
            'prev_start' => $prevStart,
            'prev_end' => $prevEnd,
            'type' => AbstractDateFilterType::TYPE_BETWEEN,
        ];
        $widgetOptions = new WidgetOptionBag(
            [
                'compareToDate' => ['useDate' => true, 'date' => null],
                'dateRange'     => $dateRange
            ]
        );

        $forecastDataCallback = function ($users, $start, $end, $moment) {
            if ($moment === null) {
                return ['inProgressCount' => 5, 'budgetAmount' => 1000, 'weightedForecast' => 500];
            }

            return ['inProgressCount' => 2, 'budgetAmount' => 200, 'weightedForecast' => 50];
        };
        $this->dateHelper
            ->expects($this->once())
            ->method('getCurrentDateTime')
            ->willReturn(new \DateTime());

        $reflection = new \ReflectionObject($this->provider);
        $method     = $reflection->getMethod('getMoment');
        $method->setAccessible(true);
        $prevMoment = $method->invokeArgs($this->provider, [$dateRange, $start]);

        $this->forecastProvider->expects($this->exactly(6))
            ->method('getForecastData')
            ->with(
                $widgetOptions,
                $this->logicalOr($start, $prevStart),
                $this->logicalOr($end, $prevEnd),
                $this->logicalOr(null, $prevMoment)
            )
            ->will($this->returnCallback($forecastDataCallback));

        $this->filterDateRangeConverter->expects($this->any())
            ->method('getViewValue')
            ->with(['start' => $prevStart, 'end' => $prevEnd, 'type' => AbstractDateFilterType::TYPE_BETWEEN])
            ->will($this->returnValue('prev range'));

        $result = $this->provider
            ->getForecastOfOpportunitiesValues($widgetOptions, 'inProgressCount', 'integer', false);
        $this->assertEquals(
            ['value' => 5, 'deviation' => '+3 (+1.5)', 'isPositive' => true, 'previousRange' => 'prev range'],
            $result
        );

        $result = $this->provider
            ->getForecastOfOpportunitiesValues($widgetOptions, 'budgetAmount', 'currency', false);
        $this->assertEquals(
            ['value' => 1000, 'deviation' => '+800 (+4)', 'isPositive' => 1, 'previousRange' => 'prev range'],
            $result
        );

        $result = $this->provider
            ->getForecastOfOpportunitiesValues($widgetOptions, 'weightedForecast', 'currency', false);
        $this->assertEquals(
            ['value' => 500, 'deviation' => '+450 (+9)', 'isPositive' => 1, 'previousRange' => 'prev range'],
            $result
        );
    }
}
