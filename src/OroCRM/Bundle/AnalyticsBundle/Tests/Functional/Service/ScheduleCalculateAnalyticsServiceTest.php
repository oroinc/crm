<?php
namespace OroCRM\Bundle\AnalyticsBundle\Tests\Functional\Service;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\TraceableMessageProducer;
use OroCRM\Bundle\AnalyticsBundle\Async\Topics;
use OroCRM\Bundle\AnalyticsBundle\Model\RFMAwareInterface;
use OroCRM\Bundle\AnalyticsBundle\Service\ScheduleCalculateAnalyticsService;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Tests\Functional\Fixture\LoadChannel;

/**
 * @dbIsolationPerTest
 */
class ScheduleCalculateAnalyticsServiceTest extends WebTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->initClient();
        $this->loadFixtures([LoadChannel::class]);
        $this->getMessageProducer()->clearTraces();
    }

    public function testCouldBeGetFromContainerAsService()
    {
        $service = $this->getContainer()->get('orocrm_analytics.schedule_calculate_analytics');

        $this->assertInstanceOf(ScheduleCalculateAnalyticsService::class, $service);
    }

    public function testShouldScheduleAnalyticsCalculateIfStatusTrueAndRFMEnabled()
    {
        // test for code written on modern yaml programming language

        /** @var Channel $channel */
        $channel = $this->getReference('default_channel');
        $channel->setStatus(false);

        $this->getEntityManager()->persist($channel);
        $this->getEntityManager()->flush();

        $channel->setStatus(true);
        $channel->setData([RFMAwareInterface::RFM_STATE_KEY => true]);

        $this->getMessageProducer()->clearTraces();

        $this->getEntityManager()->persist($channel);
        $this->getEntityManager()->flush();

        $traces = $this->getMessageProducer()->getTopicTraces(Topics::CALCULATE_CHANNEL_ANALYTICS);
        self::assertCount(1, $traces);
        self::assertEquals([
            'channel_id' => $channel->getId(),
            'customer_ids' => [],
        ], $traces[0]['message']);
        self::assertEquals(MessagePriority::VERY_LOW, $traces[0]['priority']);
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
