<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Provider;

use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\DashboardBundle\Helper\DateHelper;
use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\DashboardBundle\Provider\Converters\FilterDateRangeConverter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractDateFilterType;
use Oro\Bundle\LocaleBundle\Formatter\NumberFormatter;
use Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatter;
use Oro\Bundle\UserBundle\Dashboard\OwnerHelper;

use OroCRM\Bundle\SalesBundle\Provider\Opportunity\ForecastProvider;
use OroCRM\Bundle\SalesBundle\Provider\ForecastOfOpportunities;

class ForecastOfOpportunitiesTest extends \PHPUnit_Framework_TestCase
{
    /** @var ForecastOfOpportunities */
    protected $provider;

    /** @var TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $translator;

    /** @var NumberFormatter|\PHPUnit_Framework_MockObject_MockObject */
    protected $numberFormatter;

    /** @var DateTimeFormatter|\PHPUnit_Framework_MockObject_MockObject */
    protected $dateTimeFormatter;

    /** @var ForecastProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $forecastProvider;

    /** @var DateHelper|\PHPUnit_Framework_MockObject_MockObject */
    protected $dateHelper;

    /** @var OwnerHelper|\PHPUnit_Framework_MockObject_MockObject */
    protected $ownerHelper;

    /** @var FilterDateRangeConverter|\PHPUnit_Framework_MockObject_MockObject */
    protected $filterDateRangeConverter;

    protected function setUp()
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

        $this->dateTimeFormatter = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatter')
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

        $this->ownerHelper = $this->getMockBuilder('Oro\Bundle\UserBundle\Dashboard\OwnerHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->forecastProvider = $this
            ->getMockBuilder('OroCRM\Bundle\SalesBundle\Provider\Opportunity\ForecastProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->ownerHelper->expects($this->any())
            ->method('getOwnerIds')
            ->willReturn([]);

        $this->filterDateRangeConverter = $this
            ->getMockBuilder('Oro\Bundle\DashboardBundle\Provider\Converters\FilterDateRangeConverter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider = new ForecastOfOpportunities(
            $this->numberFormatter,
            $this->dateTimeFormatter,
            $this->translator,
            $this->dateHelper,
            $this->ownerHelper,
            $this->forecastProvider,
            $this->filterDateRangeConverter
        );
    }

    public function tearDown()
    {
        unset(
            $this->numberFormatter,
            $this->dateTimeFormatter,
            $this->translator,
            $this->dateHelper,
            $this->ownerHelper,
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
            ->with([], null, null, null, [])
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
                [],
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
