<?php

namespace OroCRM\Bundle\AnalyticsBundle\Tests\Functional\Command;

use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageCollector;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Client\MessagePriority;
use OroCRM\Bundle\AnalyticsBundle\Async\Topics;
use OroCRM\Bundle\AnalyticsBundle\Tests\Functional\DataFixtures\LoadCustomerData;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;

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

    public function testShouldOutputHelpForTheCommand()
    {
        $result = $this->runCommand('oro:cron:analytic:calculate', ['--help']);

        self::assertContains("Usage:\n  oro:cron:analytic:calculate [options]", $result);
    }

    public function testShouldScheduleCalculateAnalyticsForGivenChannel()
    {
        /** @var Channel $channel */
        $channel = $this->getReference('Channel.CustomerChannel');

        $result = $this->runCommand('oro:cron:analytic:calculate', ['--channel='.$channel->getId()]);

        self::assertContains('Schedule analytics calculation for "'.$channel->getId().'" channel.', $result);
        self::assertContains('Completed', $result);

        $traces = $this->getMessageProducer()->getTopicSentMessages(Topics::CALCULATE_CHANNEL_ANALYTICS);

        self::assertCount(1, $traces);
        self::assertEquals([
            'channel_id' => $channel->getId(),
            'customer_ids' => []
        ], $traces[0]['message']->getBody());
        self::assertEquals(MessagePriority::VERY_LOW, $traces[0]['message']->getPriority());
    }

    public function testShouldScheduleAnalyticsCalculationForAllAvailableChannels()
    {
        $result = $this->runCommand('oro:cron:analytic:calculate');

        self::assertContains('Schedule analytics calculation for all channels.', $result);
        self::assertContains('Completed', $result);

        $traces = $this->getMessageProducer()->getTopicSentMessages(Topics::CALCULATE_ALL_CHANNELS_ANALYTICS);

        self::assertCount(1, $traces);
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

        $traces = $this->getMessageProducer()->getTopicSentMessages(Topics::CALCULATE_CHANNEL_ANALYTICS);

        self::assertCount(1, $traces);
        self::assertEquals([
            'channel_id' => $channel->getId(),
            'customer_ids' => [$customerOne->getId(), $customerTwo->getId()]
        ], $traces[0]['message']->getBody());
        self::assertEquals(MessagePriority::VERY_LOW, $traces[0]['message']->getPriority());
    }

    /**
     * @return MessageCollector
     */
    private function getMessageProducer()
    {
        return self::getContainer()->get('oro_message_queue.message_producer');
    }
}
