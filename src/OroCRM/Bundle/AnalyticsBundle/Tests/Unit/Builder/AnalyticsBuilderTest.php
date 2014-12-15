<?php

namespace OroCRM\Bundle\AnalyticsBundle\Tests\Unit\Builder;

use OroCRM\Bundle\AnalyticsBundle\Builder\AnalyticsBuilder;
use OroCRM\Bundle\AnalyticsBundle\Builder\AnalyticsBuilderInterface;
use OroCRM\Bundle\AnalyticsBundle\Model\AnalyticsAwareInterface;

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

    public function testAddAndGetBuilders()
    {
        $builders = [$this->getBuilderMock(), $this->getBuilderMock()];

        foreach ($builders as $builder) {
            $this->builder->addBuilder($builder);
        }

        $this->assertEquals(
            $builders,
            $this->builder->getBuilders()
        );
    }

    /**
     * @param array $builders
     * @param bool $expected
     *
     * @dataProvider buildDataProvider
     */
    public function testBuild(array $builders, $expected)
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|AnalyticsAwareInterface $entity */
        $entity = $this->getMock('OroCRM\Bundle\AnalyticsBundle\Model\AnalyticsAwareInterface');

        foreach ($builders as $builder) {
            $this->builder->addBuilder($builder);
        }

        $this->assertEquals(
            $expected,
            $this->builder->build($entity)
        );
    }

    /**
     * @return array
     */
    public function buildDataProvider()
    {
        return [
            [[$this->getNotSupportedBuilder()], false],
            [[$this->getSupportedBuilder(false)], false],
            [[$this->getSupportedBuilder(true)], true],
            [[$this->getNotSupportedBuilder(), $this->getNotSupportedBuilder()], false],
            [[$this->getNotSupportedBuilder(), $this->getSupportedBuilder(false)], false],
            [[$this->getNotSupportedBuilder(), $this->getSupportedBuilder(true)], true],
            [[$this->getSupportedBuilder(false), $this->getNotSupportedBuilder()], false],
            [[$this->getSupportedBuilder(true), $this->getNotSupportedBuilder()], true],
            [[$this->getSupportedBuilder(false), $this->getSupportedBuilder(false)], false],
            [[$this->getSupportedBuilder(false), $this->getSupportedBuilder(true)], true],
            [[$this->getSupportedBuilder(true), $this->getSupportedBuilder(false)], true],
            [[$this->getSupportedBuilder(true), $this->getSupportedBuilder(true)], true],
        ];
    }

    /**
     * @param bool $result
     * @return \PHPUnit_Framework_MockObject_MockObject|AnalyticsBuilderInterface
     */
    protected function getSupportedBuilder($result)
    {
        $supportedBuilder = $this->getBuilderMock();

        $supportedBuilder
            ->expects($this->any())
            ->method('supports')
            ->will($this->returnValue(true));

        $supportedBuilder
            ->expects($this->once())
            ->method('build')
            ->will($this->returnValue($result));

        return $supportedBuilder;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AnalyticsBuilderInterface
     */
    protected function getNotSupportedBuilder()
    {
        $notSupportedBuilder = $this->getBuilderMock();

        $notSupportedBuilder
            ->expects($this->any())
            ->method('supports')
            ->will($this->returnValue(false));

        $notSupportedBuilder
            ->expects($this->never())
            ->method('build');

        return $notSupportedBuilder;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AnalyticsBuilderInterface
     */
    protected function getBuilderMock()
    {
        return $this->getMock('OroCRM\Bundle\AnalyticsBundle\Builder\AnalyticsBuilderInterface');
    }
}
