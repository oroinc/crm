<?php
namespace Oro\Bundle\AnalyticsBundle\Tests\Functional\Async;

use Oro\Bundle\AnalyticsBundle\Async\CalculateChannelAnalyticsProcessor;
use Oro\Bundle\AnalyticsBundle\Async\Topic\CalculateChannelAnalyticsTopic;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;

/**
 * @dbIsolationPerTest
 */
class CalculateChannelAnalyticsProcessorTest extends WebTestCase
{
    use MessageQueueExtension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->initClient();

        if (!\class_exists('Oro\Bundle\MagentoBundle\OroMagentoBundle', false)) {
            self::markTestSkipped('There is no suitable channel data in the system.');
        }

        $this->loadFixtures([
            'Oro\Bundle\MagentoBundle\Tests\Functional\DataFixtures\LoadCustomerData',
            'Oro\Bundle\MagentoBundle\Tests\Functional\DataFixtures\LoadRFMMetricCategoryData',
        ]);
    }

    public function testCouldBeGetFromContainerAsService(): void
    {
        $processor = self::getContainer()->get('oro_analytics.async.calculate_channel_analytics_processor');

        $this->assertInstanceOf(CalculateChannelAnalyticsProcessor::class, $processor);
    }

    public function testProcessChannelNotFound(): void
    {
        $sentMessage = self::sendMessage(
            CalculateChannelAnalyticsTopic::getName(),
            ['channel_id' => PHP_INT_MAX]
        );
        self::consumeMessage($sentMessage);

        self::assertProcessedMessageStatus(MessageProcessorInterface::REJECT, $sentMessage);
        self::assertProcessedMessageProcessor(
            'oro_analytics.async.calculate_channel_analytics_processor',
            $sentMessage
        );
        self::assertTrue(
            self::getLoggerTestHandler()->hasError('Channel not found: ' . PHP_INT_MAX)
        );
    }

    public function testProcessChannelNotActive(): void
    {
        $channelId = $this->getReference('Channel.AnalyticsAwareInterface')->getId();

        $sentMessage = self::sendMessage(
            CalculateChannelAnalyticsTopic::getName(),
            ['channel_id' => $channelId]
        );
        self::consumeMessage($sentMessage);

        self::assertProcessedMessageStatus(MessageProcessorInterface::REJECT, $sentMessage);
        self::assertProcessedMessageProcessor(
            'oro_analytics.async.calculate_channel_analytics_processor',
            $sentMessage
        );
        self::assertTrue(
            self::getLoggerTestHandler()->hasError('Channel not active: ' . $channelId)
        );
    }

    public function testProcessChannelNotSupposedToCalculate(): void
    {
        $channelId = $this->getReference('Channel.CustomerIdentity')->getId();

        $sentMessage = self::sendMessage(
            CalculateChannelAnalyticsTopic::getName(),
            ['channel_id' => $channelId]
        );
        self::consumeMessage($sentMessage);

        self::assertProcessedMessageStatus(MessageProcessorInterface::REJECT, $sentMessage);
        self::assertProcessedMessageProcessor(
            'oro_analytics.async.calculate_channel_analytics_processor',
            $sentMessage
        );
        self::assertTrue(
            self::getLoggerTestHandler()->hasError('Channel is not supposed to calculate analytics: ' . $channelId)
        );
    }

    /**
     * @dataProvider processDataProvider
     */
    public function testProcess(array $initialData, array $expectedData): void
    {
        /** @var Channel $channel */
        $channel = $this->getReference('Channel.CustomerChannel');

        $this->assertAnalytics($channel, $initialData);

        $sentMessage = self::sendMessage(
            CalculateChannelAnalyticsTopic::getName(),
            ['channel_id' => $channel->getId()]
        );
        self::consumeMessage($sentMessage);

        self::assertProcessedMessageStatus(MessageProcessorInterface::ACK, $sentMessage);
        self::assertProcessedMessageProcessor(
            'oro_analytics.async.calculate_channel_analytics_processor',
            $sentMessage
        );

        $this->assertAnalytics($channel, $expectedData);
    }

    public function processDataProvider(): array
    {
        return [
            [
                'initialData' => [
                    'Channel.CustomerIdentity.CustomerIdentity' => [
                        'recency' => 1,
                        'frequency' => 1,
                        'monetary' => 1,
                    ],
                    'Channel.CustomerChannel.Customer' => [
                        'recency' => 2,
                        'frequency' => 2,
                        'monetary' => 2,
                    ],
                ],
                'expectedData' => [
                    'Channel.CustomerIdentity.CustomerIdentity' => [
                        'recency' => 10,
                        'frequency' => 9,
                        'monetary' => 8,
                    ],
                    'Channel.CustomerChannel.Customer' => [
                        'recency' => 10,
                        'frequency' => 9,
                        'monetary' => 8,
                    ],
                ]
            ]
        ];
    }

    private function assertAnalytics(Channel $channel, array $expectedData): void
    {
        $expectedData = array_combine(array_map(function ($item) {
            return $this->getReference($item)->getId();
        }, array_keys($expectedData)), array_values($expectedData));

        $repository = self::getContainer()->get('doctrine')
            ->getManagerForClass('Oro\Bundle\MagentoBundle\Entity\Customer')
            ->getRepository('Oro\Bundle\MagentoBundle\Entity\Customer');

        $actualData = array_reduce(
            $repository->findBy(['dataChannel' => $channel]),
            static function ($result, $item) {
                $result[$item->getId()] = [
                    'recency' => $item->getRecency(),
                    'frequency' => $item->getFrequency(),
                    'monetary' => $item->getMonetary(),
                ];

                return $result;
            },
            []
        );

        self::assertEquals($expectedData, $actualData);
    }
}
