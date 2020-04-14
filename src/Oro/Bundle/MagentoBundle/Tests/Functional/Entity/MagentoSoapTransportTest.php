<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\IntegrationBundle\Async\Topics;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Client\MessagePriority;

/**
 * @dbIsolationPerTest
 */
class MagentoSoapTransportTest extends WebTestCase
{
    use MessageQueueExtension;

    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadMagentoChannel::class]);
    }

    public function testShouldScheduleSyncIntegrationIfSyncStartDateIsChanged()
    {
        /** @var Channel $integration */
        $integration = $this->getReference('integration');

        /** @var MagentoSoapTransport $transport */
        $transport = $integration->getTransport();

        //guard
        self::assertInstanceOf(MagentoSoapTransport::class, $transport);

        $transport->setSyncStartDate(new \DateTime('2010-01-01'));

        $this->getEntityManager()->flush();

        $traces = self::getMessageCollector()->getTopicSentMessages(Topics::SYNC_INTEGRATION);

        self::assertCount(1, $traces);

        self::assertEquals([
            'integration_id' => $integration->getId(),
            'connector_parameters' => [],
            'connector' => null,
            'transport_batch_size' => 100,
        ], $traces[0]['message']->getBody());
        self::assertEquals(MessagePriority::VERY_LOW, $traces[0]['message']->getPriority());
    }

    /**
     * @return EntityManagerInterface
     */
    private function getEntityManager()
    {
        return self::getContainer()->get('doctrine.orm.entity_manager');
    }
}
