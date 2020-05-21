<?php
namespace Oro\Bundle\MagentoBundle\Tests\Functional\Command;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Async\Topics;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;

/**
 * @dbIsolationPerTest
 */
class SyncCartExpirationCommandTest extends WebTestCase
{
    use MessageQueueExtension;

    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadMagentoChannel::class]);
    }

    public function testShouldSendSyncIntegrationWithoutAnyAdditionalOptions()
    {
        /** @var Channel $integration */
        $integration = $this->getReference('integration');

        $result = $this->runCommand('oro:cron:magento:cart:expiration', ['--channel-id='.$integration->getId()]);

        static::assertStringContainsString('Run sync for "Demo Web store" channel', $result);
        static::assertStringContainsString('Completed', $result);

        self::assertMessageSent(
            Topics::SYNC_CART_EXPIRATION_INTEGRATION,
            new Message(
                ['integrationId' => $integration->getId()],
                MessagePriority::VERY_LOW
            )
        );
    }
}
