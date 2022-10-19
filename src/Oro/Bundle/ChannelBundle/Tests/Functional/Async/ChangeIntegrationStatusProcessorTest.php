<?php

namespace Oro\Bundle\ChannelBundle\Tests\Functional\Async;

use Oro\Bundle\CacheBundle\Adapter\ChainAdapter;
use Oro\Bundle\ChannelBundle\Async\ChangeIntegrationStatusProcessor;
use Oro\Bundle\ChannelBundle\Async\Topic\ChannelStatusChangedTopic;
use Oro\Bundle\ChannelBundle\Tests\Functional\Fixture\LoadChannelsWithDataSource;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;

/**
 * @dbIsolationPerTest
 */
class ChangeIntegrationStatusProcessorTest extends WebTestCase
{
    use MessageQueueExtension;

    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadChannelsWithDataSource::class]);
    }

    public function testCouldBeGetFromContainerAsService(): void
    {
        $processor = self::getContainer()->get('oro_channel.async.change_integration_status_processor');

        self::assertInstanceOf(ChangeIntegrationStatusProcessor::class, $processor);
    }

    public function testProcessChannelNotFound(): void
    {
        $sentMessage = self::sendMessage(
            ChannelStatusChangedTopic::getName(),
            ['channelId' => PHP_INT_MAX]
        );
        self::consumeMessage($sentMessage);

        self::assertProcessedMessageStatus(MessageProcessorInterface::REJECT, $sentMessage);
        self::assertProcessedMessageProcessor('oro_channel.async.change_integration_status_processor', $sentMessage);
        self::assertTrue(
            self::getLoggerTestHandler()->hasCritical('Channel not found: ' . PHP_INT_MAX)
        );
    }

    public function testProcess(): void
    {
        /** @var Integration $dataSource */
        $dataSource = $this->getReference('oro_integration:foo_integration');

        self::assertTrue($dataSource->isEnabled());
        self::assertEquals(Integration::EDIT_MODE_ALLOW, $dataSource->getEditMode());

        // Warms up data into the cache
        self::getContainer()->get('oro_channel.provider.state_provider')->isEntityEnabled(\stdClass::class);

        /** @var ChainAdapter $cacheChainAdapter */
        $cacheChainAdapter = self::getContainer()->get('oro_channel.state_cache');
        // Checks that cache is warmed up
        self::assertTrue($cacheChainAdapter->hasItem('oro_channel_state_data_'));

        $sentMessage = self::sendMessage(
            ChannelStatusChangedTopic::getName(),
            ['channelId' => $this->getReference('default_channel')->getId()]
        );
        self::consumeMessage($sentMessage);

        self::assertProcessedMessageStatus(MessageProcessorInterface::ACK, $sentMessage);
        self::assertProcessedMessageProcessor('oro_channel.async.change_integration_status_processor', $sentMessage);

        self::assertFalse($dataSource->isEnabled());
        self::assertEquals(Integration::EDIT_MODE_DISALLOW, $dataSource->getEditMode());

        self::assertFalse($cacheChainAdapter->hasItem('oro_channel_state_data_'));
    }
}
