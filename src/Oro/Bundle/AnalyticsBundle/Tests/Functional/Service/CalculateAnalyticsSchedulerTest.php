<?php
namespace Oro\Bundle\AnalyticsBundle\Tests\Functional\Service;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\AnalyticsBundle\Async\Topic\CalculateChannelAnalyticsTopic;
use Oro\Bundle\AnalyticsBundle\Model\RFMAwareInterface;
use Oro\Bundle\AnalyticsBundle\Service\CalculateAnalyticsScheduler;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Tests\Functional\Fixture\LoadChannel;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Client\MessagePriority;

/**
 * @dbIsolationPerTest
 */
class CalculateAnalyticsSchedulerTest extends WebTestCase
{
    use MessageQueueExtension;

    protected function setUp(): void
    {
        $this->initClient();

        $this->getOptionalListenerManager()->enableListener('oro_workflow.listener.event_trigger_collector');

        $this->loadFixtures([LoadChannel::class]);
    }

    public function testCouldBeGetFromContainerAsService(): void
    {
        $service = self::getContainer()->get('oro_analytics.calculate_analytics_scheduler');

        $this->assertInstanceOf(CalculateAnalyticsScheduler::class, $service);
    }

    /**
     * Test for analytics_channel_calculate_rfm process
     */
    public function testShouldScheduleAnalyticsCalculateIfStatusTrueAndRFMEnabled(): void
    {
        /** @var Channel $channel */
        $channel = $this->getReference('default_channel');
        $channel->setStatus(false);

        $this->getEntityManager()->persist($channel);
        $this->getEntityManager()->flush();

        $channel->setStatus(true);
        $channel->setData([RFMAwareInterface::RFM_STATE_KEY => true]);

        self::getMessageCollector()->clear();

        $this->getEntityManager()->persist($channel);
        $this->getEntityManager()->flush();

        self::assertMessagesCount(CalculateChannelAnalyticsTopic::getName(), 1);
        self::assertMessageSent(CalculateChannelAnalyticsTopic::getName(), [
            'channel_id' => $channel->getId(),
            'customer_ids' => [],
        ]);
        self::assertMessageSentWithPriority(CalculateChannelAnalyticsTopic::getName(), MessagePriority::VERY_LOW);
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return self::getContainer()->get('doctrine.orm.entity_manager');
    }
}
