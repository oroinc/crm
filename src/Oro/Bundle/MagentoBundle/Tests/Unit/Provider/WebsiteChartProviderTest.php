<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider;

use Oro\Bundle\ChartBundle\Model\ChartViewBuilder;
use Oro\Bundle\MagentoBundle\Provider\TrackingVisitEventProvider;
use Symfony\Component\Translation\Translator;

abstract class WebsiteChartProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param string $method Mocked method
     * @param array $returnData
     *
     * @return TrackingVisitEventProvider
     */
    protected function getTrackingVisitEventProviderMock($method, array $returnData)
    {
        $eventProvider = $this->getMockBuilder(TrackingVisitEventProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $eventProvider->expects($this->any())
            ->method($method)
            ->willReturn($returnData);

        return $eventProvider;
    }

    /**
     * @param array $expectedData
     * @param array $expectedOptions
     *
     * @return ChartViewBuilder
     */
    protected function getChartViewBuilderMock(array $expectedData, array $expectedOptions)
    {
        $chartViewBuilder = $this->getMockBuilder(ChartViewBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $chartViewBuilder->expects($this->once())
            ->method('setArrayData')
            ->with($this->equalTo($expectedData))
            ->willReturn($chartViewBuilder);

        $chartViewBuilder->expects($this->once())
            ->method('setOptions')
            ->with($this->equalTo($expectedOptions))
            ->willReturn($chartViewBuilder);

        return $chartViewBuilder;
    }

    protected function getTranslator()
    {
        $translator = $this->getMockBuilder(Translator::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $translator->expects($this->any())
            ->method('trans')
            ->willReturnArgument(0);

        return $translator;
    }
}
