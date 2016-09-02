<?php
namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Command;

use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\TraceableMessageProducer;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\MagentoBundle\Async\Topics;
use OroCRM\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel;

/**
 * @dbIsolationPerTest
 */
class InitialSyncCommandTest extends WebTestCase
{
    use MessageQueueExtension;

    public function setUp()
    {
        $this->initClient();
        $this->loadFixtures([LoadMagentoChannel::class]);
        $this->getMessageProducer()->clear();
    }

    public function testShouldOutputHelpForTheCommand()
    {
        $result = $this->runCommand('oro:magento:initial:sync', ['--help']);

        $this->assertContains("Usage:\n  oro:magento:initial:sync [options]", $result);
    }

    public function testShouldSendSyncIntegrationWithoutAnyAdditionalOptions()
    {
        /** @var Channel $channel */
        $channel = $this->getReference('default_channel');
        $integration = $channel->getDataSource();

        $result = $this->runCommand('oro:magento:initial:sync', ['--integration='.$integration->getId()]);

        $this->assertContains('Run initial sync for "Demo Web store" integration.', $result);
        $this->assertContains('Completed', $result);

        $traces = $this->getMessageProducer()->getTopicSentMessages(Topics::SYNC_INITIAL_INTEGRATION);

        $this->assertCount(1, $traces);

        $this->assertEquals([
            'integration_id' => $integration->getId(),
            'connector_parameters' => [],
            'connector' => null,
        ], $traces[0]['message']->getBody());
        $this->assertEquals(MessagePriority::VERY_LOW, $traces[0]['message']->getPriority());
    }

    public function testShouldSendSyncIntegrationWithCustomConnectorAndOptions()
    {
        /** @var Channel $channel */
        $channel = $this->getReference('default_channel');
        $integration = $channel->getDataSource();

        $result = $this->runCommand('oro:magento:initial:sync', [
            '--integration='.$integration->getId(),
            '--connector' => 'theConnector',
            'fooConnectorOption=fooValue',
            'barConnectorOption=barValue',
        ]);

        $this->assertContains('Run initial sync for "Demo Web store" integration.', $result);
        $this->assertContains('Completed', $result);

        $traces = $this->getMessageProducer()->getTopicSentMessages(Topics::SYNC_INITIAL_INTEGRATION);

        $this->assertCount(1, $traces);

        $this->assertEquals([
            'integration_id' => $integration->getId(),
            'connector_parameters' => [
                'fooConnectorOption' => 'fooValue',
                'barConnectorOption' => 'barValue',
            ],
            'connector' => 'theConnector',
        ], $traces[0]['message']->getBody());
        $this->assertEquals(MessagePriority::VERY_LOW, $traces[0]['message']->getPriority());
    }

    /**
     * @return TraceableMessageProducer
     */
    private function getMessageProducer()
    {
        return self::getContainer()->get('oro_message_queue.message_producer');
    }
}
