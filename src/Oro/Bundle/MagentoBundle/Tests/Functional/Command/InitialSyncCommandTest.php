<?php
namespace Oro\Bundle\MagentoBundle\Tests\Functional\Command;

use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Async\Topics;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel;

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

        self::assertMessageSent(
            Topics::SYNC_INITIAL_INTEGRATION,
            new Message(
                [
                    'integration_id' => $integration->getId(),
                    'connector_parameters' => [],
                    'connector' => null,
                ],
                MessagePriority::VERY_LOW
            )
        );
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

        self::assertMessageSent(
            Topics::SYNC_INITIAL_INTEGRATION,
            new Message(
                [
                    'integration_id' => $integration->getId(),
                    'connector_parameters' => [
                        'fooConnectorOption' => 'fooValue',
                        'barConnectorOption' => 'barValue',
                    ],
                    'connector' => 'theConnector',
                ],
                MessagePriority::VERY_LOW
            )
        );
    }
}
