<?php

namespace Oro\Bundle\AnalyticsBundle\Tests\Functional\Builder;

use Oro\Bundle\AnalyticsBundle\Builder\RFMBuilder;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class RFMBuilderTest extends WebTestCase
{
    /** @var RFMBuilder */
    private $builder;

    protected function setUp(): void
    {
        $this->initClient();

        if (!\class_exists('Oro\Bundle\MagentoBundle\OroMagentoBundle', false)) {
            self::markTestSkipped('There is no suitable channel data in the system.');
        }

        $this->loadFixtures([
            'Oro\Bundle\MagentoBundle\Tests\Functional\DataFixtures\LoadCustomerData',
            'Oro\Bundle\MagentoBundle\Tests\Functional\DataFixtures\LoadRFMMetricCategoryData',
            'Oro\Bundle\MagentoBundle\Tests\Functional\DataFixtures\LoadOrderData',
        ]);

        $this->builder = $this->getContainer()->get('oro_analytics.builder.rfm');
    }

    /**
     * @dataProvider buildDataProvider
     */
    public function testBuild($channelReference, array $expectedData, array $ids = [])
    {
        /** @var Channel $channel */
        $channel = $this->getReference($channelReference);
        $this->builder->build($channel, $ids);
        $this->assertAnalyticBuild($channel, $expectedData);
    }

    public function buildDataProvider(): array
    {
        return [
            [
                'channelReference' => 'Channel.CustomerChannel',
                'expectedData' => [
                    'Channel.CustomerIdentity.CustomerIdentity' => [
                        'recency' => 10,
                        'frequency' => null,
                        'monetary' => 8,
                    ],
                    'Channel.CustomerChannel.Customer' => [
                        'recency' => null,
                        'frequency' => 9,
                        'monetary' => 8,
                    ],
                ]
            ]
        ];
    }

    private function assertAnalyticBuild(Channel $channel, array $expectedData): void
    {
        $expectedData = array_combine(array_map(function ($item) {
            return $this->getReference($item)->getId();
        }, array_keys($expectedData)), array_values($expectedData));

        $repository = $this->getContainer()->get('doctrine')
            ->getManagerForClass('Oro\Bundle\MagentoBundle\Entity\Customer')
            ->getRepository('Oro\Bundle\MagentoBundle\Entity\Customer');

        $actualData = $repository->findBy(['dataChannel' => $channel]);
        /** @var \Oro\Bundle\MagentoBundle\Entity\Customer[] $actualData */
        $actualData = array_reduce($actualData, function ($result, $item) {
            /** @var \Oro\Bundle\MagentoBundle\Entity\Customer $item */
            $result[$item->getId()] = [
                'recency' => $item->getRecency(),
                'frequency' => $item->getFrequency(),
                'monetary' => $item->getMonetary(),
            ];
            return $result;
        }, []);
        $this->assertCount(count($expectedData), $actualData);
        $this->assertEquals($expectedData, $actualData);
    }
}
