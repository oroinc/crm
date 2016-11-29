<?php

namespace Oro\Bundle\AnalyticsBundle\Tests\Functional\Command;

use Oro\Bundle\AnalyticsBundle\Async\Topics;
use Oro\Bundle\AnalyticsBundle\Tests\Functional\DataFixtures\LoadCustomerData;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Customer;
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

    protected function setUp()
    {
        parent::setUp();

        $this->initClient();
        $this->loadFixtures([LoadCustomerData::class]);
    }

    public function testShouldScheduleCalculateAnalyticsForGivenChannel()
    {
        /** @var Channel $channel */
        $channel = $this->getReference('Channel.CustomerChannel');

        $result = $this->runCommand('oro:cron:analytic:calculate', ['--channel='.$channel->getId()]);

        self::assertContains('Schedule analytics calculation for "'.$channel->getId().'" channel.', $result);
        self::assertContains('Completed', $result);

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

    public function testShouldScheduleAnalyticsCalculationForAllAvailableChannels()
    {
        $result = $this->runCommand('oro:cron:analytic:calculate');

        self::assertContains('Schedule analytics calculation for all channels.', $result);
        self::assertContains('Completed', $result);

        self::assertMessageSent(
            Topics::CALCULATE_ALL_CHANNELS_ANALYTICS,
            new Message([], MessagePriority::VERY_LOW)
        );
    }

    public function testThrowIfCustomerIdsSetWithoutChannelId()
    {
        $result = $this->runCommand('oro:cron:analytic:calculate', ['--ids=1', '--ids=2']);

        self::assertContains('[InvalidArgumentException]', $result);
        self::assertContains('Option "ids" does not work without "channel"', $result);
    }

    public function testShouldScheduleCalculateAnalyticsForGivenChannelWithCustomerIdsSet()
    {
        /** @var Channel $channel */
        $channel = $this->getReference('Channel.CustomerChannel');

        /** @var Customer $customerOne */
        $customerOne = $this->getReference('Channel.CustomerChannel.Customer');

        /** @var Customer $customerTwo */
        $customerTwo = $this->getReference('Channel.CustomerChannel.Customer2');

        $result = $this->runCommand('oro:cron:analytic:calculate', [
            '--channel='.$channel->getId(),
            '--ids='.$customerOne->getId(),
            '--ids='.$customerTwo->getId(),
        ]);

        self::assertContains('Schedule analytics calculation for "'.$channel->getId().'" channel.', $result);
        self::assertContains('Completed', $result);

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
