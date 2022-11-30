<?php

namespace Oro\Bundle\ChannelBundle\Tests\Functional\Command;

use Oro\Bundle\ChannelBundle\Async\Topic\AggregateLifetimeAverageTopic;
use Oro\Bundle\ChannelBundle\Tests\Functional\Fixture\LoadLifetimeHistoryData;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Client\MessagePriority;

/**
 * @dbIsolationPerTest
 */
class LifetimeAverageAggregateCommandTest extends WebTestCase
{
    use MessageQueueExtension;

    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadLifetimeHistoryData::class]);
    }

    public function testShouldSendAggregateLifetimeAverageMessage(): void
    {
        $result = self::runCommand('oro:cron:lifetime-average:aggregate', []);

        self::assertStringContainsString('Completed!', $result);

        self::assertMessageSent(
            AggregateLifetimeAverageTopic::getName(),
            ['force' => false, 'use_truncate' => true]
        );
        self::assertMessageSentWithPriority(AggregateLifetimeAverageTopic::getName(), MessagePriority::VERY_LOW);
    }
}
