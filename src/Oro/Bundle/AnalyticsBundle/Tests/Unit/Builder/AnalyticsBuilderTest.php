<?php

namespace Oro\Bundle\AnalyticsBundle\Tests\Unit\Builder;

use Oro\Bundle\AnalyticsBundle\Builder\AnalyticsBuilder;
use Oro\Bundle\AnalyticsBundle\Builder\AnalyticsBuilderInterface;
use Oro\Bundle\ChannelBundle\Entity\Channel;

class AnalyticsBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider buildDataProvider
     */
    public function testBuild(array $builders)
    {
        $entity = $this->createMock(Channel::class);

        $builder = new AnalyticsBuilder($builders);
        $builder->build($entity);
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
     * @return AnalyticsBuilderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getSupportedBuilder($supported = true)
    {
        $supportedBuilder = $this->createMock(AnalyticsBuilderInterface::class);
        $supportedBuilder->expects($this->any())
            ->method('supports')
            ->will($this->returnValue($supported));
        if ($supported) {
            $supportedBuilder->expects($this->once())
                ->method('build');
        }

        return $supportedBuilder;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|AnalyticsBuilderInterface
     */
    private function getNotSupportedBuilder()
    {
        return $this->getSupportedBuilder(false);
    }
}
