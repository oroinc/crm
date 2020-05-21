<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\AnalyticsBundle\Async\Topics;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Tests\Functional\Fixture\LoadChannel;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Entity\Status;
use Oro\Bundle\IntegrationBundle\Tests\Functional\DataFixtures\LoadChannelData as LoadIntegrationData;
use Oro\Bundle\MagentoBundle\Provider\MagentoChannelType;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Client\MessagePriority;

/**
 * @dbIsolationPerTest
 */
class StatusTest extends WebTestCase
{
    use MessageQueueExtension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->initClient();
        $this->loadFixtures([LoadChannel::class, LoadIntegrationData::class]);
    }

    /**
     * test for magento_analytics_customer_calculate_imported process
     */
    public function testShouldScheduleAnalyticsCalculateWhenCompletedIntegrationStatusIsCreated()
    {
        /** @var Channel $channel */
        $channel = $this->getReference('default_channel');
        /** @var Integration $integration */
        $integration = $this->getReference('oro_integration:foo_integration');
        $channel->setDataSource($integration);
        $channel->setStatus(Channel::STATUS_ACTIVE);
        $channel->setData(['rfm_enabled' => true]);
        $this->getEntityManager()->flush();
        self::getMessageCollector()->clear();

        /** @var Status $status */
        $status = new Status();
        $status->setChannel($integration);
        $status->setCode(Status::STATUS_COMPLETED);
        $status->setDate(new \DateTime('2012-01-01 00:00:00+00:00'));
        $status->setMessage('');
        $status->getChannel()->setType(MagentoChannelType::TYPE);
        $status->setConnector('order');
        $this->getEntityManager()->persist($status);
        $this->getEntityManager()->flush();

        $traces = self::getMessageCollector()->getTopicSentMessages(Topics::CALCULATE_CHANNEL_ANALYTICS);
        self::assertCount(1, $traces);
        self::assertEquals([
            'channel_id' => $channel->getId(),
            'customer_ids' => [],
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
