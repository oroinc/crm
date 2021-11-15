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

    public function buildDataProvider(): array
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

    private function getSupportedBuilder(bool $supported = true): AnalyticsBuilderInterface
    {
        $supportedBuilder = $this->createMock(AnalyticsBuilderInterface::class);
        $supportedBuilder->expects($this->any())
            ->method('supports')
            ->willReturn($supported);
        if ($supported) {
            $supportedBuilder->expects($this->once())
                ->method('build');
        }

        return $supportedBuilder;
    }

    private function getNotSupportedBuilder(): AnalyticsBuilderInterface
    {
        return $this->getSupportedBuilder(false);
    }
}
