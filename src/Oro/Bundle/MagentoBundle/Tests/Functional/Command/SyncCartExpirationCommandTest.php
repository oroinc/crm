<?php
namespace Oro\Bundle\MagentoBundle\Tests\Functional\Command;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Bundle\MagentoBundle\Async\Topics;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel;

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

        self::assertMessageSent(
            Topics::SYNC_CART_EXPIRATION_INTEGRATION,
            new Message(
                ['integrationId' => $integration->getId()],
                MessagePriority::VERY_LOW
            )
        );
    }
}
