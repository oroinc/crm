<?php

namespace Oro\Bundle\ChannelBundle\Tests\Functional\Command;

use Oro\Bundle\ChannelBundle\Async\Topics;
use Oro\Bundle\ChannelBundle\Tests\Functional\Fixture\LoadLifetimeHistoryData;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;

/**
 * @dbIsolationPerTest
 */
class LifetimeAverageAggregateCommandTest extends WebTestCase
{
    use MessageQueueExtension;

    protected function setUp()
    {
        $this->initClient();
        $this->loadFixtures([LoadLifetimeHistoryData::class]);
    }

    public function testShouldSendAggregateLifetimeAverageMessage()
    {
        $result = $this->runCommand('oro:cron:lifetime-average:aggregate', []);

        $this->assertContains('Completed!', $result);

        self::assertMessageSent(
            Topics::AGGREGATE_LIFETIME_AVERAGE,
            new Message(
                ['force' => false, 'use_truncate' => true],
                MessagePriority::VERY_LOW
            )
        );
    }
}
