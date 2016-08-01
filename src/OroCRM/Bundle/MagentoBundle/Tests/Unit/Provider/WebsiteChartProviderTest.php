<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider;

use Symfony\Component\DependencyInjection\Container;

use Oro\Bundle\ChartBundle\Model\ChartViewBuilder;

use OroCRM\Bundle\MagentoBundle\Provider\TrackingVisitEventProvider;

abstract class WebsiteChartProviderTest extends \PHPUnit_Framework_TestCase
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

    /**
     * @param ChartViewBuilder $chartViewBuilder
     *
     * @return ContainerInterface
     */
    protected function getContainer(ChartViewBuilder $chartViewBuilder)
    {
        $container = new Container();
        $container->set('oro_chart.view_builder', $chartViewBuilder);

        return $container;
    }
}
