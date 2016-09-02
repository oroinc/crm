<?php
namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Command;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\TraceableMessageProducer;
use OroCRM\Bundle\MagentoBundle\Async\Topics;
use OroCRM\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel;

/**
 * @dbIsolationPerTest
 */
class SyncCartExpirationCommandTest extends WebTestCase
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
        $result = $this->runCommand('oro:cron:magento:cart:expiration', ['--help']);

        $this->assertContains("Usage:\n  oro:cron:magento:cart:expiration [options]", $result);
    }

    public function testShouldSendSyncIntegrationWithoutAnyAdditionalOptions()
    {
        /** @var Channel $integration */
        $integration = $this->getReference('integration');

        $result = $this->runCommand('oro:cron:magento:cart:expiration', ['--channel-id='.$integration->getId()]);

        $this->assertContains('Run sync for "Demo Web store" channel', $result);
        $this->assertContains('Completed', $result);

        $traces = $this->getMessageProducer()->getTopicSentMessages(Topics::SYNC_CART_EXPIRATION_INTEGRATION);

        $this->assertCount(1, $traces);

        $this->assertEquals([
            'integrationId' => $integration->getId(),
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
