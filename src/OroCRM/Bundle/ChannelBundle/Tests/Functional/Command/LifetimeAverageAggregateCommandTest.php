<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Functional\Command;

use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\TraceableMessageProducer;
use OroCRM\Bundle\ChannelBundle\Async\Topics;
use OroCRM\Bundle\ChannelBundle\Tests\Functional\Fixture\LoadLifetimeHistoryData;

/**
 * @outputBuffering false
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

    public function testShouldOutputCommandHelp()
    {
        $result = $this->runCommand('oro:cron:lifetime-average:aggregate', ['--help']);

        $this->assertContains("Usage:\n  oro:cron:lifetime-average:aggregate [options]", $result);
    }

    public function testShouldSendAggregateLifetimeAverageMessage()
    {
        $result = $this->runCommand('oro:cron:lifetime-average:aggregate', []);

        $this->assertContains('Completed!', $result);

        $traces = $this->getMessageProducer()->getTopicSentMessages(Topics::AGGREGATE_LIFETIME_AVERAGE);

        $this->assertCount(1, $traces);
        $this->assertEquals(['force' => false, 'clear_table_use_delete' => false], $traces[0]['message']->getBody());
        $this->assertEquals(MessagePriority::VERY_LOW, $traces[0]['message']->getPriority());
    }

    /**
     * @return TraceableMessageProducer
     */
    private function getMessageProducer()
    {
        return $this->getContainer()->get('oro_message_queue.message_producer');
    }
}
