<?php

namespace Oro\Bundle\AnalyticsBundle\Tests\Unit\Builder;

use Oro\Bundle\AnalyticsBundle\Builder\AnalyticsBuilder;
use Oro\Bundle\AnalyticsBundle\Builder\AnalyticsBuilderInterface;
use Oro\Bundle\ChannelBundle\Entity\Channel;

class AnalyticsBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AnalyticsBuilder
     */
    protected $builder;

    protected function setUp()
    {
        $this->builder = new AnalyticsBuilder();
    }

    /**
     * @param array $builders
     *
     * @dataProvider buildDataProvider
     */
    public function testBuild(array $builders)
    {
        /** @var Channel|\PHPUnit_Framework_MockObject_MockObject $entity */
        $entity = $this->getMock('Oro\Bundle\ChannelBundle\Entity\Channel');

        foreach ($builders as $builder) {
            $this->builder->addBuilder($builder);
        }

        $this->builder->build($entity);
    }

    /**
     * @return array
     */
    public function buildDataProvider()
    {
        return [
            [[$this->getNotSupportedBuilder()]],
            [[$this->getSupportedBuilder()]],
            [[$this->getSupportedBuilder()]],
            [[$this->getNotSupportedBuilder(), $this->getNotSupportedBuilder()]],
            [[$this->getNotSupportedBuilder(), $this->getSupportedBuilder()]],
            [[$this->getNotSupportedBuilder(), $this->getSupportedBuilder()]],
            [[$this->getSupportedBuilder(), $this->getNotSupportedBuilder()]],
            [[$this->getSupportedBuilder(), $this->getNotSupportedBuilder()]],
            [[$this->getSupportedBuilder(), $this->getSupportedBuilder()]],
            [[$this->getSupportedBuilder(), $this->getSupportedBuilder()]],
            [[$this->getSupportedBuilder(), $this->getSupportedBuilder()]],
            [[$this->getSupportedBuilder(), $this->getSupportedBuilder()]],
        ];
    }

    /**
     * @param bool $supported
     * @return AnalyticsBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSupportedBuilder($supported = true)
    {
        $supportedBuilder = $this->getBuilderMock();

        $supportedBuilder
            ->expects($this->any())
            ->method('supports')
            ->will($this->returnValue($supported));

        if ($supported) {
            $supportedBuilder
                ->expects($this->once())
                ->method('build');
        }

        return $supportedBuilder;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AnalyticsBuilderInterface
     */
    protected function getNotSupportedBuilder()
    {
        return $this->getSupportedBuilder(false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AnalyticsBuilderInterface
     */
    protected function getBuilderMock()
    {
        return $this->getMock('Oro\Bundle\AnalyticsBundle\Builder\AnalyticsBuilderInterface');
    }
}
