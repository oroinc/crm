<?php
namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Entity\Status;
use Oro\Bundle\IntegrationBundle\Tests\Functional\DataFixtures\LoadChannelData as LoadIntegrationData;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\TraceableMessageProducer;
use OroCRM\Bundle\AnalyticsBundle\Async\Topics;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Tests\Functional\Fixture\LoadChannel;

/**
 * @dbIsolationPerTest
 */
class StatusTest extends WebTestCase
{
    use MessageQueueExtension;

    public function setUp()
    {
        parent::setUp();

        $this->initClient();
        $this->loadFixtures([LoadChannel::class, LoadIntegrationData::class]);
        $this->getMessageProducer()->clear();
    }

    public function testShouldScheduleAnalyticsCalculateWhenCompletedIntegrationStatusIsCreated()
    {
        // test for code written on modern yaml programming language
        // magento_analytics_customer_calculate_imported from process.yml

        /** @var Channel $channel */
        $channel = $this->getReference('default_channel');
        /** @var Integration $integration */
        $integration = $this->getReference('oro_integration:foo_integration');
        $channel->setDataSource($integration);
        $this->getEntityManager()->flush();
        $this->getMessageProducer()->clear();

        /** @var Status $status */
        $status = new Status();
        $status->setChannel($integration);
        $status->setCode(Status::STATUS_COMPLETED);
        $status->setDate(new \DateTime('2012-01-01 00:00:00+00:00'));
        $status->setMessage('');
        $status->getChannel()->setType('magento');
        $status->setConnector('order');

        $this->getEntityManager()->persist($status);
        $this->getEntityManager()->flush();

        $traces = $this->getMessageProducer()->getTopicSentMessages(Topics::CALCULATE_CHANNEL_ANALYTICS);
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

    /**
     * @return TraceableMessageProducer
     */
    private function getMessageProducer()
    {
        return self::getContainer()->get('oro_message_queue.message_producer');
    }
}
