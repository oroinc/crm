<?php

namespace Oro\Bundle\AnalyticsBundle\Tests\Functional\Command;

use Oro\Bundle\AnalyticsBundle\Async\Topics;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;

/**
 * @dbIsolationPerTest
 */
class CalculateAnalyticsCommandTest extends WebTestCase
{
    use MessageQueueExtension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->initClient();

        if (!\class_exists('Oro\Bundle\MagentoBundle\OroMagentoBundle', false)) {
            static::markTestSkipped('There is no suitable channel data in the system.');
            return;
        }

        $this->loadFixtures(['Oro\Bundle\MagentoBundle\Tests\Functional\DataFixtures\LoadCustomerData']);
    }

    public function testShouldScheduleCalculateAnalyticsForGivenChannel()
    {
        /** @var Channel $channel */
        $channel = $this->getReference('Channel.CustomerChannel');

        $result = $this->runCommand('oro:cron:analytic:calculate', ['--channel='.$channel->getId()]);

        static::assertStringContainsString(
            \sprintf('Schedule analytics calculation for "%s" channel.', $channel->getId()),
            $result
        );
        static::assertStringContainsString('Completed', $result);

        self::assertMessageSent(
            Topics::CALCULATE_CHANNEL_ANALYTICS,
            new Message(
                [
                    'channel_id' => $channel->getId(),
                    'customer_ids' => []
                ],
                MessagePriority::VERY_LOW
            )
        );
    }

    public function testShouldNotScheduleCalculateAnalyticsForNonSupportedChannel()
    {
        /** @var Channel $channel */
        $channel = $this->getReference('Channel.CustomerIdentity');
        $channelId = $channel->getId();

        $result = $this->runCommand('oro:cron:analytic:calculate', ['--channel=' . $channelId]);

        static::assertStringContainsString('Schedule analytics calculation for "'. $channelId.'" channel.', $result);
        static::assertStringContainsString(
            \sprintf('Channel is not supposed to calculate analytics: %s', $channelId),
            $result
        );
    }

    public function testShouldNotScheduleCalculateAnalyticsForNonActiveChannel()
    {
        /** @var Channel $channel */
        $channel = $this->getReference('Channel.AnalyticsAwareInterface');
        $channelId = $channel->getId();

        $result = $this->runCommand('oro:cron:analytic:calculate', ['--channel=' . $channelId]);

        static::assertStringContainsString('Schedule analytics calculation for "'. $channelId . '" channel.', $result);
        static::assertStringContainsString(sprintf('Channel not active: %s', $channelId), $result);
    }

    public function testShouldScheduleAnalyticsCalculationForAllAvailableChannels()
    {
        $result = $this->runCommand('oro:cron:analytic:calculate');

        static::assertStringContainsString('Schedule analytics calculation for all channels.', $result);
        static::assertStringContainsString('Completed', $result);

        self::assertMessageSent(
            Topics::CALCULATE_ALL_CHANNELS_ANALYTICS,
            new Message([], MessagePriority::VERY_LOW)
        );
    }

    public function testThrowIfCustomerIdsSetWithoutChannelId()
    {
        $result = $this->runCommand('oro:cron:analytic:calculate', ['--ids=1', '--ids=2']);

        static::assertStringContainsString('In CalculateAnalyticsCommand.php', $result);
        static::assertStringContainsString('Option "ids" does not work without "channel"', $result);
    }

    public function testShouldScheduleCalculateAnalyticsForGivenChannelWithCustomerIdsSet()
    {
        /** @var Channel $channel */
        $channel = $this->getReference('Channel.CustomerChannel');

        /** @var \Oro\Bundle\MagentoBundle\Entity\Customer $customerOne */
        $customerOne = $this->getReference('Channel.CustomerChannel.Customer');

        /** @var \Oro\Bundle\MagentoBundle\Entity\Customer $customerTwo */
        $customerTwo = $this->getReference('Channel.CustomerChannel.Customer2');

        $result = $this->runCommand('oro:cron:analytic:calculate', [
            '--channel='.$channel->getId(),
            '--ids='.$customerOne->getId(),
            '--ids='.$customerTwo->getId(),
        ]);

        static::assertStringContainsString(
            \sprintf('Schedule analytics calculation for "%s" channel.', $channel->getId()),
            $result
        );
        static::assertStringContainsString('Completed', $result);

        self::assertMessageSent(
            Topics::CALCULATE_CHANNEL_ANALYTICS,
            new Message(
                [
                    'channel_id' => $channel->getId(),
                    'customer_ids' => [$customerOne->getId(), $customerTwo->getId()]
                ],
                MessagePriority::VERY_LOW
            )
        );
    }
}
